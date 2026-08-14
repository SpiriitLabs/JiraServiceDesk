<?php

namespace App\Tests\Unit\Controller\App;

use App\Controller\App\LocaleController;
use App\Enum\User\Locale;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class LocaleControllerTest extends TestCase
{
    public static function localeDataProvider(): \Generator
    {
        yield 'switch to english' => ['switchToEnglish', Locale::EN];

        yield 'switch to french' => ['switchToFrench', Locale::FR];
    }

    #[Test]
    #[DataProvider('localeDataProvider')]
    public function testItMustStoreLocaleInSessionAndRedirectToReferer(string $method, Locale $expectedLocale): void
    {
        $session = new Session(new MockArraySessionStorage());
        $request = Request::create('/locale/'.$expectedLocale->value);
        $request->headers->set('referer', 'https://service-desk.test/projects');
        $request->setSession($session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $response = (new LocaleController())->{$method}($requestStack, $request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://service-desk.test/projects', $response->getTargetUrl());
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame($expectedLocale->value, $session->get('_locale'));
    }

    #[Test]
    #[DataProvider('localeDataProvider')]
    public function testItMustOverrideThePreviouslyStoredLocale(string $method, Locale $expectedLocale): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('_locale', 'de');

        $request = Request::create('/locale/'.$expectedLocale->value);
        $request->headers->set('referer', 'https://service-desk.test/');
        $request->setSession($session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        (new LocaleController())->{$method}($requestStack, $request);

        $this->assertSame($expectedLocale->value, $session->get('_locale'));
    }
}
