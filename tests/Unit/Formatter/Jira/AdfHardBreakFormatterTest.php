<?php

namespace App\Tests\Unit\Formatter\Jira;

use App\Formatter\Jira\AdfHardBreakFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AdfHardBreakFormatterTest extends TestCase
{

    public static function adfHardBreakProvider(): \Generator
    {
        yield 'no content' => [
            ['type' => 'doc'],
            ['type' => 'doc']
        ];

        yield 'no hardBreak only paragraph' => [
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'hardBreak'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'hardBreak'],
                        ],
                    ],
                ],
            ],
        ];

        yield 'two hard break paragraphs' => [
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'hardBreak'],
                        ],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'hardBreak'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'hardBreak'],
                            ['type' => 'hardBreak'],
                        ],
                    ],
                ],
            ],
        ];

        yield 'mixed content' => [
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => 'Hello'],
                        ],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'hardBreak'],
                        ],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'hardBreak'],
                        ],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => 'World'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => 'Hello'],
                            ['type' => 'hardBreak'],
                            ['type' => 'hardBreak'],
                        ],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => 'World'],
                        ],
                    ],
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('adfHardBreakProvider')]
    public function testFormat(array $input, array $expected): void
    {
        self::assertSame(
            $expected,
            AdfHardBreakFormatter::format($input),
        );
    }
}
