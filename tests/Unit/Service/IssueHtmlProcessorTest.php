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

    #[Test]
    public function testUpdateMediaEmbedsReplacesLegacyPluginWithVideo(): void
    {
        $body = '<p><div class="embeddedObject">'
            . '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" '
            . 'data="/rest/api/3/attachment/content/72510?stream=true" type="video/quicktime" height="380" width="954">'
            . '<param name="src" value="/rest/api/3/attachment/content/72510?stream=true"/>'
            . '<embed src="/rest/api/3/attachment/content/72510?stream=true" type="video/quicktime"/>'
            . '</object></div></p>';

        $this->router
            ->expects(self::once())
            ->method('generate')
            ->with('app_attachment', [
                'key' => 'PROJECT',
                'keyIssue' => 'PROJECT-123',
                'attachmentId' => '72510',
            ])
            ->willReturn('/app/attachment/PROJECT/PROJECT-123/72510')
        ;

        $service = $this->generate();
        $updatedBody = $service->updateMediaEmbeds($body, 'PROJECT-123');

        $this->assertStringContainsString('<video', $updatedBody);
        $this->assertStringContainsString('src="/app/attachment/PROJECT/PROJECT-123/72510"', $updatedBody);
        $this->assertStringContainsString('controls', $updatedBody);
        $this->assertStringNotContainsString('<object', $updatedBody);
        $this->assertStringNotContainsString('clsid', $updatedBody);
    }

    #[Test]
    public function testUpdateMediaEmbedsWithoutIssueKeyLeavesHtmlUntouched(): void
    {
        $body = '<object data="/rest/api/3/attachment/content/72510" type="video/quicktime"></object>';

        $this->router
            ->expects(self::never())
            ->method('generate')
        ;

        $service = $this->generate();

        self::assertSame($body, $service->updateMediaEmbeds($body));
    }

    private function generate(): IssueHtmlProcessor
    {
        return new IssueHtmlProcessor(
            $this->router,
        );
    }
}
