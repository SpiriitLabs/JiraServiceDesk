<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdfImageProcessor
{
    /**
     * Extract base64 images from ADF content and return them as temp files.
     *
     * @param array<mixed> $adfData
     *
     * @return array{adf: array<mixed>, files: array<array{tempPath: string, mimeType: string, index: int}>}
     */
    public static function extractBase64Images(array $adfData): array
    {
        $files = [];
        $index = 0;
        $adfData = self::walkAndExtract($adfData, $files, $index);

        return [
            'adf' => $adfData,
            'files' => $files,
        ];
    }

    /**
     * Replace placeholder image nodes with proper media references.
     *
     * @param array<mixed>                                    $adfData
     * @param array<int, array{id: string, filename: string}> $attachmentRefs
     *
     * @return array<mixed>
     */
    public static function replaceWithAttachments(array $adfData, array $attachmentRefs): array
    {
        return self::walkAndReplace($adfData, $attachmentRefs);
    }

    /**
     * Create an UploadedFile from base64 data.
     */
    public static function createTempFileFromBase64(string $base64Data, string $mimeType, int $index): UploadedFile
    {
        $extension = match (true) {
            str_contains($mimeType, 'png') => 'png',
            str_contains($mimeType, 'gif') => 'gif',
            str_contains($mimeType, 'webp') => 'webp',
            str_contains($mimeType, 'svg') => 'svg',
            default => 'jpg',
        };
        $filename = sprintf('pasted-image-%d.%s', $index, $extension);
        $tmpPath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tmpPath, base64_decode($base64Data));

        return new UploadedFile(
            path: $tmpPath,
            originalName: $filename,
            mimeType: $mimeType,
            error: null,
            test: true,
        );
    }

    /**
     * Whitelist valid ADF attributes for media nodes and clean up TipTap artifacts.
     *
     * @param array<mixed> $adfData
     *
     * @return array<mixed>
     */
    public static function sanitizeMediaAttrs(array $adfData): array
    {
        return self::walkAndSanitize($adfData);
    }

    /**
     * Clean ADF structure for Jira: remove empty attrs/content/marks arrays.
     *
     * @param array<mixed> $adfData
     *
     * @return array<mixed>
     */
    public static function cleanForJira(array $adfData): array
    {
        return self::walkAndClean($adfData);
    }

    /**
     * Convert any stray 'image' nodes (TipTap format) to ADF 'mediaSingle > media' format.
     * This is a PHP-side safety net in case the JS conversion misses some nodes.
     *
     * @param array<mixed> $adfData
     *
     * @return array<mixed>
     */
    public static function normalizeImageNodes(array $adfData): array
    {
        return self::walkAndNormalize($adfData);
    }

    /**
     * @param array<mixed> $node
     *
     * @return array<mixed>
     */
    private static function walkAndSanitize(array $node): array
    {
        // Whitelist only valid ADF attributes for media nodes.
        // type "file": id, type, collection, occurrenceKey
        // type "external": type, url
        if (isset($node['type']) && $node['type'] === 'media' && isset($node['attrs'])) {
            $allowedMediaAttrs = ['id', 'type', 'collection', 'occurrenceKey', 'url'];
            $node['attrs'] = array_intersect_key(
                $node['attrs'],
                array_flip($allowedMediaAttrs),
            );
        }

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $key => $child) {
                $node['content'][$key] = self::walkAndSanitize($child);
            }
        }

        return $node;
    }

    /**
     * @param array<mixed> $node
     *
     * @return array<mixed>
     */
    private static function walkAndClean(array $node): array
    {
        // Remove null values from attrs
        if (array_key_exists('attrs', $node) && is_array($node['attrs'])) {
            $node['attrs'] = array_filter($node['attrs'], static fn ($v) => $v !== null);
        }

        // Convert TipTap orderedList "start" attr to Jira ADF "order"
        if (isset($node['type']) && $node['type'] === 'orderedList' && isset($node['attrs']['start'])) {
            $node['attrs']['order'] = $node['attrs']['start'];
            unset($node['attrs']['start']);
        }

        // Remove empty attrs
        if (array_key_exists('attrs', $node) && $node['attrs'] === []) {
            unset($node['attrs']);
        }

        // Sanitize link mark attrs: Jira ADF only accepts 'href' and 'title'
        // TipTap adds 'target', 'rel', 'class' which Jira rejects as INVALID_INPUT
        if (array_key_exists('marks', $node) && is_array($node['marks'])) {
            $node['marks'] = array_map(static function (array $mark): array {
                if ($mark['type'] === 'link' && isset($mark['attrs'])) {
                    $mark['attrs'] = array_intersect_key(
                        $mark['attrs'],
                        array_flip(['href', 'title']),
                    );
                }

                return $mark;
            }, $node['marks']);
        }

        // Remove empty marks
        if (array_key_exists('marks', $node) && $node['marks'] === []) {
            unset($node['marks']);
        }

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $key => $child) {
                $node['content'][$key] = self::walkAndClean($child);
            }
        }

        return $node;
    }

    /**
     * @param array<mixed> $node
     *
     * @return array<mixed>
     */
    private static function walkAndNormalize(array $node): array
    {
        if (isset($node['type']) && $node['type'] === 'image') {
            $src = $node['attrs']['src'] ?? '';
            $mediaId = $src;

            if (! str_starts_with($src, 'data:')) {
                if (preg_match('#/attachment/(\d+)#', $src, $matches)) {
                    $mediaId = $matches[1];
                }
            }

            $mediaSingleAttrs = [
                'layout' => 'align-start',
            ];
            if (isset($node['attrs']['width'])) {
                $mediaSingleAttrs['width'] = (int) $node['attrs']['width'];
            }

            return [
                'type' => 'mediaSingle',
                'attrs' => $mediaSingleAttrs,
                'content' => [[
                    'type' => 'media',
                    'attrs' => [
                        'id' => $mediaId,
                        'type' => 'file',
                        'collection' => '',
                    ],
                ]],
            ];
        }

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $key => $child) {
                $node['content'][$key] = self::walkAndNormalize($child);
            }
        }

        return $node;
    }

    /**
     * @param array<mixed>                                                                 $node
     * @param array<array{tempPath: string, mimeType: string, index: int, base64: string}> $files
     *
     * @return array<mixed>
     */
    private static function walkAndExtract(array $node, array &$files, int &$index): array
    {
        // Media nodes with base64 data URL in the id attribute (from TipTap image → ADF conversion)
        if (isset($node['type']) && $node['type'] === 'media' && isset($node['attrs']['id'])) {
            $id = $node['attrs']['id'];
            if (str_starts_with($id, 'data:')) {
                if (preg_match('#^data:(image/[^;]+);base64,(.+)$#', $id, $matches)) {
                    $mimeType = $matches[1];
                    $base64 = $matches[2];
                    $currentIndex = $index++;
                    $files[] = [
                        'index' => $currentIndex,
                        'mimeType' => $mimeType,
                        'base64' => $base64,
                    ];
                    $node['attrs']['id'] = '__base64_placeholder_' . $currentIndex . '__';
                }
            }
        }

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $key => $child) {
                $node['content'][$key] = self::walkAndExtract($child, $files, $index);
            }
        }

        return $node;
    }

    /**
     * @param array<mixed>                                    $node
     * @param array<int, array{id: string, filename: string}> $attachmentRefs
     *
     * @return array<mixed>
     */
    private static function walkAndReplace(array $node, array $attachmentRefs): array
    {
        if (isset($node['type']) && $node['type'] === 'media' && isset($node['attrs']['id'])) {
            $id = $node['attrs']['id'];
            if (preg_match('#^__base64_placeholder_(\d+)__$#', $id, $matches)) {
                $placeholderIndex = (int) $matches[1];
                if (isset($attachmentRefs[$placeholderIndex])) {
                    $ref = $attachmentRefs[$placeholderIndex];
                    $node['attrs']['id'] = $ref['id'];
                }
            }
        }

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $key => $child) {
                $node['content'][$key] = self::walkAndReplace($child, $attachmentRefs);
            }
        }

        return $node;
    }
}
