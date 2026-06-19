<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Routing\RouterInterface;

readonly class IssueHtmlProcessor
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function updateImageSources(string $html, string $issueKey = ''): string
    {
        // Without an issue key we cannot build the (project-scoped) secured
        // attachment URL, so leave the markup untouched.
        if ($issueKey === '') {
            return $html;
        }

        $projectKey = explode('-', $issueKey)[0];

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if (preg_match('#/rest/api/3/attachment/content/(\d+)#', $href, $matches)) {
                $attachmentId = $matches[1];
                $newUrl = $this->router->generate('app_attachment', [
                    'key' => $projectKey,
                    'keyIssue' => $issueKey,
                    'attachmentId' => $attachmentId,
                ]);
                $link->setAttribute('href', $newUrl);
            }
        }

        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            if (preg_match('#/rest/api/3/attachment/content/(\d+)#', $src, $matches)) {
                $attachmentId = $matches[1];
                $newUrl = $this->router->generate('app_attachment', [
                    'key' => $projectKey,
                    'keyIssue' => $issueKey,
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

    /**
     * Jira renders embedded videos as a legacy <object>/<embed> plugin element
     * (e.g. the QuickTime ActiveX control). Modern browsers cannot load these
     * plugins and instead show "This plug-in is not supported.", so we replace
     * them with a native HTML5 <video> element pointing to the proxied
     * attachment.
     */
    public function updateMediaEmbeds(string $html, string $issueKey = ''): string
    {
        // Without an issue key we cannot build the (project-scoped) secured
        // attachment URL, so leave the markup untouched.
        if ($issueKey === '') {
            return $html;
        }

        $projectKey = explode('-', $issueKey)[0];

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        // Snapshot the node list: we mutate the tree while iterating.
        $objects = iterator_to_array($dom->getElementsByTagName('object'));
        foreach ($objects as $object) {
            $source = $this->resolveMediaAttachmentSource($object);
            if ($source === null || ! preg_match('#/rest/api/3/attachment/content/(\d+)#', $source, $matches)) {
                continue;
            }

            $attachmentId = $matches[1];
            $newUrl = $this->router->generate('app_attachment', [
                'key' => $projectKey,
                'keyIssue' => $issueKey,
                'attachmentId' => $attachmentId,
            ]);

            $video = $dom->createElement('video');
            $video->setAttribute('controls', 'controls');
            $video->setAttribute('preload', 'metadata');
            // No type attribute on purpose: let the browser sniff the codec so
            // .mov files encoded with H.264 still play across browsers.
            $video->setAttribute('src', $newUrl);
            $video->setAttribute('style', 'max-width: 100%;');

            // Fallback for browsers that cannot play the file inline.
            $fallback = $dom->createElement('a');
            $fallback->setAttribute('href', $newUrl);
            $fallback->setAttribute('target', '_blank');
            $fallback->setAttribute('rel', 'noopener');
            $fallback->appendChild($dom->createTextNode('Télécharger la vidéo'));
            $video->appendChild($fallback);

            $object->parentNode->replaceChild($video, $object);
        }

        return $dom->saveHTML();
    }

    /**
     * Find the attachment URL carried by a legacy media <object>, looking at the
     * element itself and its nested <param>/<embed> children.
     */
    private function resolveMediaAttachmentSource(\DOMElement $object): ?string
    {
        foreach (['data', 'src'] as $attribute) {
            $value = $object->getAttribute($attribute);
            if ($value !== '') {
                return $value;
            }
        }

        foreach ($object->getElementsByTagName('param') as $param) {
            if (in_array($param->getAttribute('name'), ['data', 'src'], true)) {
                $value = $param->getAttribute('value');
                if ($value !== '') {
                    return $value;
                }
            }
        }

        foreach ($object->getElementsByTagName('embed') as $embed) {
            $value = $embed->getAttribute('src');
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    public function updateJiraLinks(string $html): string
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            // Match links like https://something.atlassian.net/browse/MMGS-96
            if (preg_match('#https:\/\/([a-zA-Z0-9\-]+)\.atlassian\.net\/browse\/([A-Z]+)-(\d+)#', $href, $matches)) {
                $projectKey = $matches[2];           // e.g., MMGS
                $issueNumber = $matches[3];          // e.g., 96
                $issueKey = $projectKey . '-' . $issueNumber;

                $focusedCommentId = null;
                if (preg_match('#focusedCommentId=(\d+)#', $href, $commentIds)) {
                    $focusedCommentId = $commentIds[1];
                }

                // Generate custom route like /jira/issue/MMGS/MMGS-96
                $newUrl = $this->router->generate('browse_issue', [
                    'keyIssue' => $issueKey,
                    'focusedCommentId' => $focusedCommentId,
                ]);

                $link->setAttribute('href', $newUrl);
            }
        }

        return $dom->saveHTML();
    }
}
