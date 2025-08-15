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

        $this->router
            ->expects(self::once())
            ->method('generate')
            ->with('app_attachment', [
                'attachmentId' => 1234,
            ])
            ->willReturn('/app/attachment/1234')
        ;

        $service = $this->generate();
        $updatedBody = $service->updateImageSources($body);

        $this->assertStringContainsString('app/attachment/1234', $updatedBody);
        $this->assertStringContainsString('max-width: 100%;', $updatedBody);
        $this->assertStringNotContainsString('height', $updatedBody);
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
