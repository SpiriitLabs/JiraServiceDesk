<?php

namespace App\Tests\Unit\Formatter\Jira;

use App\Formatter\Jira\IssueAttachmentFormatter;
use JiraCloud\Attachment\AttachmentService;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class IssueAttachmentFormatterTest extends TestCase
{
    private Filesystem $filesystem;
    private IssueAttachmentFormatter $formatter;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $uploadDir = '/var/www/uploads';

        $this->formatter = new IssueAttachmentFormatter($this->filesystem, $uploadDir);

        $mockService = $this->createMock(AttachmentService::class);
        $reflection = new \ReflectionClass($this->formatter);
        $property = $reflection->getProperty('service');
        $property->setAccessible(true);
        $property->setValue($this->formatter, $mockService);
    }

    #[Test]
    #[DataProvider('attachmentDataProvider')]
    public function testFormatVariousAttachmentCases(
        array $attachments,
        array $expectedCustomAttachments,
        array $filesystemExistsReturns = []
    ): void {
        $issue = new Issue();
        $issue->key = 'TEST-KEY';
        $issue->fields = new IssueField();
        $issue->fields->attachment = $attachments;

        if (empty($filesystemExistsReturns)) {
            $this->filesystem->method('exists')->willReturn(false);
        } else {
            $this->filesystem->method('exists')->willReturnOnConsecutiveCalls(...$filesystemExistsReturns);
        }

        $result = $this->formatter->format($issue);

        $this->assertTrue(property_exists($result, 'customAttachments'));
        $this->assertCount(count($expectedCustomAttachments), $result->customAttachments);

        foreach ($expectedCustomAttachments as $index => $expectedAttachment) {
            foreach ($expectedAttachment as $key => $value) {
                $this->assertArrayHasKey($key, $result->customAttachments[$index]);
                $this->assertSame($value, $result->customAttachments[$index][$key]);
            }
        }
    }

    public static function attachmentDataProvider(): \Generator
    {
        yield 'no attachments' => [
            'attachments' => [],
            'expectedCustomAttachments' => [],
            'filesystemExistsReturns' => [],
        ];

        yield 'single attachment with author' => [
            'attachments' => [
                (object) [
                    'id' => 123,
                    'filename' => 'test.png',
                    'self' => 'https://jira.example.com/attachment/123',
                    'author' => (object) ['displayName' => 'Test User'],
                ],
            ],
            'expectedCustomAttachments' => [
                [
                    'id' => 123,
                    'name' => 'test.png',
                    'author' => 'Test User',
                ],
            ],
            'filesystemExistsReturns' => [false, false],
        ];

        yield 'attachment missing author' => [
            'attachments' => [
                (object) [
                    'id' => 321,
                    'filename' => 'file.pdf',
                    'self' => 'https://jira.example.com/attachment/321',
                    'author' => null,
                ],
            ],
            'expectedCustomAttachments' => [
                [
                    'id' => 321,
                    'name' => 'file.pdf',
                    'author' => null,
                ],
            ],
            'filesystemExistsReturns' => [false, false],
        ];

        yield 'mixed file existence' => [
            'attachments' => [
                (object) [
                    'id' => 101,
                    'filename' => 'image1.jpg',
                    'self' => 'https://jira.example.com/attachment/101',
                    'author' => (object) ['displayName' => 'User One'],
                ],
                (object) [
                    'id' => 202,
                    'filename' => 'doc1.pdf',
                    'self' => 'https://jira.example.com/attachment/202',
                    'author' => (object) ['displayName' => 'User Two'],
                ],
            ],
            'expectedCustomAttachments' => [
                [
                    'id' => 101,
                    'name' => 'image1.jpg',
                    'author' => 'User One',
                ],
                [
                    'id' => 202,
                    'name' => 'doc1.pdf',
                    'author' => 'User Two',
                ],
            ],
            'filesystemExistsReturns' => [true, true, false, false],
        ];

        yield 'malformed attachment (missing filename)' => [
            'attachments' => [
                (object) [
                    'id' => 404,
                    // filename missing
                    'self' => 'https://jira.example.com/attachment/404',
                    'author' => (object) ['displayName' => 'No Name User'],
                ],
            ],
            'expectedCustomAttachments' => [
                [
                    'id' => 404,
                    'name' => null,
                    'author' => 'No Name User',
                ],
            ],
            'filesystemExistsReturns' => [false, false],
        ];
    }
}
