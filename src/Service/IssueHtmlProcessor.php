<?php

namespace App\Service;

use Symfony\Component\Routing\RouterInterface;

readonly class IssueHtmlProcessor
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function updateImageSources(string $html): string
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            if (preg_match('#/rest/api/3/attachment/content/(\d+)#', $src, $matches)) {
                $attachmentId = $matches[1];
                $newUrl = $this->router->generate('app_attachment', [
                    'attachmentId' => $attachmentId,
                ]);
                $img->setAttribute('src', $newUrl);
            }

            $img->removeAttribute('height');
            $style = $img->getAttribute('style');
            if (stripos($style, 'max-width') === false) {
                $style = rtrim($style, '; ') . '; max-width: 100%;';
                $img->setAttribute('style', $style);
            }
        }

        return $dom->saveHTML();
    }
}
