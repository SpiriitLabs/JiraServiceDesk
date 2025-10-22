<?php

declare(strict_types=1);

namespace App\Formatter\Jira;

class AdfHardBreakFormatter
{
    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public static function format(array $data): array
    {
        if (! isset($data['content']) || ! is_array($data['content'])) {
            return $data;
        }
        $newContent = [];
        $lastParagraphIndex = null;

        foreach ($data['content'] as $block) {
            $isHardBreakOnlyParagraph = false;
            if ($block['type'] === 'paragraph'
                && isset($block['content'])
                && count($block['content']) === 1
                && $block['content'][0]['type'] === 'hardBreak') {
                $isHardBreakOnlyParagraph = true;
            }

            if ($isHardBreakOnlyParagraph) {
                if ($lastParagraphIndex !== null) {
                    $newContent[$lastParagraphIndex]['content'][] = [
                        'type' => 'hardBreak',
                    ];
                } else {
                    $newContent[] = $block;
                    $lastParagraphIndex = count($newContent) - 1;
                }
            } else {
                $newContent[] = $block;
                if ($block['type'] === 'paragraph') {
                    $lastParagraphIndex = count($newContent) - 1;
                } else {
                    $lastParagraphIndex = null;
                }
            }
        }

        $data['content'] = $newContent;

        return $data;
    }
}
