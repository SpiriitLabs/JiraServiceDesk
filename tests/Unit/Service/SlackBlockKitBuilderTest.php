<?php

namespace App\Tests\Unit\Service;

use App\Enum\Notification\NotificationType;
use App\Service\SlackBlockKitBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class SlackBlockKitBuilderTest extends TestCase
{
    private SlackBlockKitBuilder $builder;

    protected function setUp(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturn('View in Test App');

        $this->builder = new SlackBlockKitBuilder($translator, 'Test App');
    }

    #[Test]
    public function testBuildReturnsBlocksWithHeader(): void
    {
        $blocks = $this->builder->build(
            NotificationType::ISSUE_CREATED,
            'New Issue',
            'Issue body text',
            'https://example.com/issue/1',
        );

        self::assertNotEmpty($blocks);
        self::assertSame('header', $blocks[0]['type']);
        self::assertStringContains(':new:', $blocks[0]['text']['text']);
        self::assertStringContains('New Issue', $blocks[0]['text']['text']);
    }

    #[Test]
    public function testBuildIncludesBodySection(): void
    {
        $blocks = $this->builder->build(
            NotificationType::ISSUE_UPDATED,
            'Updated Issue',
            'Updated body',
            'https://example.com/issue/1',
        );

        self::assertSame('section', $blocks[1]['type']);
        self::assertSame('Updated body', $blocks[1]['text']['text']);
    }

    #[Test]
    public function testBuildTruncatesLongBody(): void
    {
        $longBody = str_repeat('a', 600);

        $blocks = $this->builder->build(
            NotificationType::COMMENT_CREATED,
            'Comment',
            $longBody,
            'https://example.com/issue/1',
        );

        self::assertLessThanOrEqual(503, mb_strlen($blocks[1]['text']['text'])); // 500 + '...'
    }

    #[Test]
    public function testBuildWithExtraContextAddsContextBlock(): void
    {
        $blocks = $this->builder->build(
            NotificationType::COMMENT_CREATED,
            'Comment',
            'Body',
            'https://example.com/issue/1',
            extraContext: ['Author' => 'John Doe'],
        );

        $contextBlock = null;
        foreach ($blocks as $block) {
            if ($block['type'] === 'context') {
                $contextBlock = $block;

                break;
            }
        }

        self::assertNotNull($contextBlock);
        self::assertStringContains('Author', $contextBlock['elements'][0]['text']);
        self::assertStringContains('John Doe', $contextBlock['elements'][0]['text']);
    }

    #[Test]
    public function testBuildWithoutExtraContextHasNoContextBlock(): void
    {
        $blocks = $this->builder->build(
            NotificationType::ISSUE_DELETED,
            'Deleted',
            'Body',
            'https://example.com/issue/1',
        );

        foreach ($blocks as $block) {
            self::assertNotSame('context', $block['type']);
        }
    }

    #[Test]
    public function testBuildIncludesActionButton(): void
    {
        $link = 'https://example.com/issue/1';
        $blocks = $this->builder->build(
            NotificationType::ISSUE_CREATED,
            'Test',
            'Body',
            $link,
        );

        $actionsBlock = null;
        foreach ($blocks as $block) {
            if ($block['type'] === 'actions') {
                $actionsBlock = $block;

                break;
            }
        }

        self::assertNotNull($actionsBlock);
        self::assertSame($link, $actionsBlock['elements'][0]['url']);
    }

    #[Test]
    public function testBuildActionButtonUsesTranslatedLabel(): void
    {
        $blocks = $this->builder->build(
            NotificationType::ISSUE_CREATED,
            'Test',
            'Body',
            'https://example.com/issue/1',
            locale: 'fr',
        );

        $actionsBlock = null;
        foreach ($blocks as $block) {
            if ($block['type'] === 'actions') {
                $actionsBlock = $block;

                break;
            }
        }

        self::assertNotNull($actionsBlock);
        self::assertSame('View in Test App', $actionsBlock['elements'][0]['text']['text']);
    }

    #[Test]
    public function testEmojiPerNotificationType(): void
    {
        $expectations = [
            [NotificationType::ISSUE_CREATED, ':new:'],
            [NotificationType::ISSUE_UPDATED, ':pencil2:'],
            [NotificationType::ISSUE_DELETED, ':wastebasket:'],
            [NotificationType::COMMENT_CREATED, ':speech_balloon:'],
            [NotificationType::COMMENT_UPDATED, ':memo:'],
        ];

        foreach ($expectations as [$type, $emoji]) {
            $blocks = $this->builder->build($type, 'Subject', 'Body', 'https://example.com');
            self::assertStringContains($emoji, $blocks[0]['text']['text'], sprintf('Expected %s for %s', $emoji, $type->value));
        }
    }

    private static function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        self::assertTrue(str_contains($haystack, $needle), $message ?: sprintf('Failed asserting that "%s" contains "%s"', $haystack, $needle));
    }
}
