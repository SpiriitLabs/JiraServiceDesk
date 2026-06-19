<?php

namespace App\Tests\Unit\Service;

use App\Service\IssueHtmlProcessor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class IssueHtmlProcessorTest extends TestCase
{
    private RouterInterface|MockObject $router;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
    }

    #[Test]
    public function testUpdateImageSources(): void
    {
        $body = '<div>Some text and image <img src="/rest/api/3/attachment/content/1234" height="12"></div>';
        $body .= '<div><a href="/rest/api/3/attachment/content/2003">a link</a></div>';

        $this->router
            ->expects(self::exactly(2))
            ->method('generate')
            ->willReturnMap([
                [
                    'app_attachment',
                    [
                        'key' => 'PROJECT',
                        'keyIssue' => 'PROJECT-123',
                        'attachmentId' => '1234',
                    ],
                    '/app/attachment/PROJECT/PROJECT-123/1234',
                ],
                [
                    'app_attachment',
                    [
                        'key' => 'PROJECT',
                        'keyIssue' => 'PROJECT-123',
                        'attachmentId' => '2003',
                    ],
                    '/app/attachment/PROJECT/PROJECT-123/2003',
                ],
            ])
        ;

        $service = $this->generate();
        $updatedBody = $service->updateImageSources($body, 'PROJECT-123');

        $this->assertStringContainsString('app/attachment/PROJECT/PROJECT-123/1234', $updatedBody);
        $this->assertStringContainsString('app/attachment/PROJECT/PROJECT-123/2003', $updatedBody);
        $this->assertStringContainsString('max-width: 100%;', $updatedBody);
        $this->assertStringNotContainsString('height', $updatedBody);
    }

    #[Test]
    public function testUpdateImageSourcesWithoutIssueKeyLeavesHtmlUntouched(): void
    {
        $body = '<div>Some text and image <img src="/rest/api/3/attachment/content/1234" height="12"></div>';

        $this->router
            ->expects(self::never())
            ->method('generate')
        ;

        $service = $this->generate();

        self::assertSame($body, $service->updateImageSources($body));
    }

    #[Test]
    public function testUpdateJiraLinks(): void
    {
        $body = '<p><a href="https://roro.atlassian.net/browse/PROJECT-123?focusedCommentId=2003" title="smart-link" class="external-link" rel="nofollow noreferrer">https://roro.atlassian.net/browse/PROJECT-123?focusedCommentId=2003</a> </p>';

        $this->router
            ->expects(self::once())
            ->method('generate')
            ->with('browse_issue', [
                'keyIssue' => 'PROJECT-123',
                'focusedCommentId' => 2003,
            ])
            ->willReturn('/browse/PROJECT-123?focusedCommentId=2003')
        ;

        $service = $this->generate();
        $updatedBody = $service->updateJiraLinks($body);

        $this->assertStringContainsString('href="/browse/PROJECT-123?focusedCommentId=2003"', $updatedBody);
    }

    private function generate(): IssueHtmlProcessor
    {
        return new IssueHtmlProcessor(
            $this->router,
        );
    }
}
