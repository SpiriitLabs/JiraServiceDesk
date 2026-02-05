<?php

namespace App\Tests\Unit\Twig\Extensions;

use App\Service\IssueHtmlProcessor;
use App\Twig\Extensions\JiraIssueExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class JiraIssueExtensionTest extends TestCase
{
    private IssueHtmlProcessor|Stub $htmlProcessor;

    protected function setUp(): void
    {
        $this->htmlProcessor = $this->createStub(IssueHtmlProcessor::class);
    }

    #[Test]
    #[DataProvider('parseCommentAuthorProvider')]
    public function testParseCommentAuthor(string $renderedBody, ?string $expectedAuthor): void
    {
        $comment = new \stdClass();
        $comment->renderedBody = $renderedBody;

        $extension = $this->createExtension();
        $result = $extension->parseCommentAuthor($comment);

        $this->assertSame($expectedAuthor, $result);
    }

    public static function parseCommentAuthorProvider(): iterable
    {
        yield 'em-dash separator' => [
            '<p>Comment content</p> — <p>John Doe</p>',
            'John Doe',
        ];

        yield 'multiple dashes separator' => [
            '<p>Test</p><p>--------------</p><p>Super ADMIN</p>',
            'Super ADMIN',
        ];

        yield 'four dashes separator' => [
            '<p>Message</p><p>----</p><p>Author Name</p>',
            'Author Name',
        ];

        yield 'dashes with br tags' => [
            '<p>Content</p><br>--------<br><p>User Name</p>',
            'User Name',
        ];

        yield 'no separator returns null' => [
            '<p>Just a comment without signature</p>',
            null,
        ];

        yield 'less than four dashes does not split' => [
            '<p>Test</p><p>---</p><p>Not Author</p>',
            null,
        ];

        yield 'author with br tags' => [
            '<p>Content</p> — <br>Jane Smith<br>',
            'Jane Smith',
        ];

        yield 'html entities decoded' => [
            '<p>Content</p> &mdash; <p>Encoded Author</p>',
            'Encoded Author',
        ];
    }

    #[Test]
    public function testTimeEstimateInHour(): void
    {
        $extension = $this->createExtension();

        $this->assertSame('1 h', $extension->timeEstimateInHour(3600));
        $this->assertSame('2.5 h', $extension->timeEstimateInHour(9000));
        $this->assertSame('0 h', $extension->timeEstimateInHour(0));
    }

    private function createExtension(): JiraIssueExtension
    {
        return new JiraIssueExtension($this->htmlProcessor);
    }
}
