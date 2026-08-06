<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\IssueHtmlProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Verrouille la liste blanche de config/packages/html_sanitizer.yaml : le HTML rendu
 * par Jira est affiché en `|raw`, ce sanitizer est donc la dernière barrière contre
 * un XSS stocké (audit sécurité §4.2).
 */
class IssueHtmlSanitizerTest extends KernelTestCase
{
    private HtmlSanitizerInterface $sanitizer;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var HtmlSanitizerInterface $sanitizer */
        $sanitizer = self::getContainer()->get('html_sanitizer.sanitizer.jira_description');
        $this->sanitizer = $sanitizer;
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function dangerousMarkupProvider(): iterable
    {
        yield 'script tag' => ['<p>ok</p><script>alert(1)</script>', 'alert(1)'];
        yield 'event handler' => ['<img src="/app/attachment/P/P-1/12" onerror="alert(1)">', 'onerror'];
        yield 'inline click handler' => ['<p onclick="alert(1)">texte</p>', 'onclick'];
        yield 'javascript scheme' => ['<a href="javascript:alert(1)">clic</a>', 'javascript:'];
        yield 'svg onload' => ['<svg onload="alert(1)"></svg>', 'onload'];
        yield 'iframe' => ['<iframe src="https://evil.tld"></iframe>', 'iframe'];
        yield 'style tag' => ['<style>body{display:none}</style><p>ok</p>', 'display:none'];
        yield 'form injection' => ['<form action="https://evil.tld"><input name="p"></form>', '<form'];
        yield 'data uri image' => ['<img src="data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=">', 'data:'];
        // `style` n'est autorisé que sur img/video : pas de superposition en position fixe.
        yield 'style hors media' => ['<div style="position:fixed;top:0;left:0">x</div>', 'position:fixed'];
    }

    #[Test]
    #[DataProvider('dangerousMarkupProvider')]
    public function testDangerousMarkupIsRemoved(string $html, string $mustNotRemain): void
    {
        self::assertStringNotContainsString($mustNotRemain, $this->sanitizer->sanitize($html));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function legitimateMarkupProvider(): iterable
    {
        yield 'texte mis en forme' => ['<p>un <strong>gras</strong> et un <em>italique</em></p>', '<strong>gras</strong>'];
        yield 'liste' => ['<ul><li>un</li><li>deux</li></ul>', '<li>un</li>'];
        yield 'tableau' => ['<table><tbody><tr><th>A</th><td colspan="2">B</td></tr></tbody></table>', 'colspan="2"'];
        // Le sanitizer encode les entités (`"` -> `&#34;`) : on vérifie la structure.
        yield 'bloc de code' => ['<pre class="content"><code>echo "x";</code></pre>', '<pre class="content"><code>echo '];
        yield 'image proxifiee' => ['<img src="/app/attachment/P/P-1/12" alt="capture">', '/app/attachment/P/P-1/12'];
        yield 'lien interne relatif' => ['<a href="/browse/PROJ-12">PROJ-12</a>', '/browse/PROJ-12'];
        yield 'lien externe' => ['<a href="https://example.com">ext</a>', 'https://example.com'];
        yield 'lien mailto' => ['<a href="mailto:a@b.tld">mail</a>', 'href="mailto:a&#64;b.tld"'];
        // Jira s'appuie sur `class` pour ses panneaux de code, tableaux et émoticônes.
        yield 'classes jira' => ['<span class="status-macro aui-lozenge">EN COURS</span>', 'class="status-macro aui-lozenge"'];
        yield 'date jira' => ['<time datetime="2026-01-05">5 janv.</time>', '<time datetime="2026-01-05">'];
        yield 'citation' => ['<blockquote><p>cité</p></blockquote>', '<blockquote>'];
    }

    #[Test]
    #[DataProvider('legitimateMarkupProvider')]
    public function testLegitimateMarkupIsPreserved(string $html, string $mustRemain): void
    {
        self::assertStringContainsString($mustRemain, $this->sanitizer->sanitize($html));
    }

    /**
     * La vidéo produite par IssueHtmlProcessor::updateMediaEmbeds() doit traverser le
     * sanitizer intacte, sans quoi les pièces jointes vidéo cesseraient de s'afficher.
     */
    #[Test]
    public function testRewrittenVideoEmbedSurvivesSanitizing(): void
    {
        $html = '<video controls="controls" preload="metadata" src="/app/attachment/P/P-1/9" style="max-width: 100%;">'
            . '<a href="/app/attachment/P/P-1/9" target="_blank" rel="noopener">Télécharger la vidéo</a>'
            . '</video>';

        $sanitized = $this->sanitizer->sanitize($html);

        self::assertStringContainsString('<video', $sanitized);
        self::assertStringContainsString('src="/app/attachment/P/P-1/9"', $sanitized);
        self::assertStringContainsString('controls', $sanitized);
    }

    /**
     * IssueHtmlProcessor injecte `max-width: 100%` en style inline sur les images : le
     * sanitizer doit conserver l'attribut, sinon les captures d'écran débordent.
     */
    #[Test]
    public function testInlineStyleIsKeptOnImages(): void
    {
        $sanitized = $this->sanitizer->sanitize('<img src="/app/attachment/P/P-1/12" style="max-width: 100%;">');

        self::assertStringContainsString('max-width: 100%', $sanitized);
    }

    #[Test]
    public function testExternalLinksAreForcedToNoopener(): void
    {
        $sanitized = $this->sanitizer->sanitize('<a href="https://example.com">ext</a>');

        self::assertStringContainsString('rel="noopener noreferrer"', $sanitized);
    }

    /**
     * Pipeline complet tel qu'appliqué par le filtre Twig `preview_description` :
     * les réécritures doivent survivre à la sanitisation, et l'injection être neutralisée.
     */
    #[Test]
    public function testFullPipelineRewritesThenSanitizes(): void
    {
        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');
        // En CLI le contexte du routeur hérite d'un baseUrl parasite (":"), absent en
        // requête HTTP réelle : on le neutralise pour générer les mêmes URLs qu'en prod.
        $router->getContext()->setBaseUrl('');

        $processor = new IssueHtmlProcessor($router, $this->sanitizer);

        $html = '<p>Capture : <img src="/rest/api/3/attachment/content/1234" height="40"></p>'
            . '<object data="/rest/api/3/attachment/content/9"></object>'
            . '<p onclick="alert(1)">légende</p><script>alert(2)</script>';

        $result = $processor->updateImageSources($html, 'PROJ-1');
        $result = $processor->updateMediaEmbeds($result, 'PROJ-1');
        $result = $processor->updateJiraLinks($result);
        $result = $processor->sanitize($result);

        // Réécritures préservées
        self::assertStringContainsString('/app/attachment/PROJ/PROJ-1/1234', $result);
        self::assertStringContainsString('<video', $result);
        self::assertStringContainsString('/app/attachment/PROJ/PROJ-1/9', $result);
        self::assertStringContainsString('max-width: 100%', $result);

        // Injection neutralisée
        self::assertStringNotContainsString('onclick', $result);
        self::assertStringNotContainsString('alert(2)', $result);

        // Le wrapper ajouté par DOMDocument::saveHTML() est retiré au passage.
        self::assertStringNotContainsString('<!DOCTYPE', $result);
        self::assertStringNotContainsString('<body', $result);
    }
}
