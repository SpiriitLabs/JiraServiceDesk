<?php

declare(strict_types=1);

namespace App\Tests\Unit\Webhook\Parser;

use App\Message\Event\Webhook\Comment\CommentCreated;
use App\Message\Event\Webhook\Comment\CommentUpdated;
use App\Message\Event\Webhook\Issue\IssueCreated;
use App\Message\Event\Webhook\Issue\IssueDeleted;
use App\Message\Event\Webhook\Issue\IssueUpdated;
use App\Repository\Jira\IssueRepository;
use App\Webhook\Parser\JiraRequestParser;
use App\Webhook\WebhookLabelFilter;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use JiraCloud\JiraException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Webhook\Exception\RejectWebhookException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class JiraRequestParserTest extends TestCase
{
    private EventDispatcherInterface|MockObject $dispatcher;

    private MessageBusInterface|MockObject $commandBus;

    private IssueRepository|MockObject $issueRepository;

    private WebhookLabelFilter|MockObject $webhookLabelFilter;

    private const string WEBHOOK_SECRET = 'test-secret';

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->commandBus = $this->createMock(MessageBusInterface::class);
        $this->issueRepository = $this->createMock(IssueRepository::class);
        $this->webhookLabelFilter = $this->createMock(WebhookLabelFilter::class);
    }

    /**
     * @return \Generator<string, array{string, class-string}>
     */
    public static function webhookEventDataProvider(): \Generator
    {
        yield 'issue_created with matching label' => [
            'jira:issue_created',
            IssueCreated::class,
        ];

        yield 'issue_updated with matching label' => [
            'jira:issue_updated',
            IssueUpdated::class,
        ];

        yield 'issue_deleted with matching label' => [
            'jira:issue_deleted',
            IssueDeleted::class,
        ];

        yield 'comment_created with matching label' => [
            'comment_created',
            CommentCreated::class,
        ];

        yield 'comment_updated with matching label' => [
            'comment_updated',
            CommentUpdated::class,
        ];
    }

    #[Test]
    #[DataProvider('webhookEventDataProvider')]
    public function testWebhookWithMatchingLabelReturnsEvent(string $webhookEvent, string $expectedEventClass): void
    {
        $this->webhookLabelFilter
            ->method('hasMatchingLabel')
            ->with(['from-client'])
            ->willReturn(true);

        $parser = $this->createParser();
        $request = $this->createValidRequest($webhookEvent, ['from-client']);

        $result = $parser->parse($request, self::WEBHOOK_SECRET);

        self::assertInstanceOf($expectedEventClass, $result);
    }

    #[Test]
    public function testWebhookWithNoMatchingLabelThrowsRejectException(): void
    {
        $this->webhookLabelFilter
            ->method('hasMatchingLabel')
            ->with(['bug', 'feature'])
            ->willReturn(false);

        $parser = $this->createParser();
        $request = $this->createValidRequest('jira:issue_created', ['bug', 'feature']);

        $this->expectException(RejectWebhookException::class);
        $this->expectExceptionMessage('Issue does not have any matching labels.');

        $parser->parse($request, self::WEBHOOK_SECRET);
    }

    #[Test]
    public function testWebhookWithMissingLabelsFieldFetchesFromApi(): void
    {
        $issue = new Issue();
        $issue->fields = new IssueField();
        $issue->fields->labels = ['from-api'];

        $this->issueRepository
            ->expects(self::once())
            ->method('getFull')
            ->with('TEST-123', [], false)
            ->willReturn($issue);

        $this->webhookLabelFilter
            ->method('hasMatchingLabel')
            ->with(['from-api'])
            ->willReturn(true);

        $parser = $this->createParser();
        $request = $this->createValidRequestWithoutLabels('comment_created');

        $result = $parser->parse($request, self::WEBHOOK_SECRET);

        self::assertInstanceOf(CommentCreated::class, $result);
    }

    #[Test]
    public function testWebhookWithMissingLabelsAndApiReturnsNoMatchingLabelsThrowsRejectException(): void
    {
        $issue = new Issue();
        $issue->fields = new IssueField();
        $issue->fields->labels = ['other-label'];

        $this->issueRepository
            ->expects(self::once())
            ->method('getFull')
            ->with('TEST-123', [], false)
            ->willReturn($issue);

        $this->webhookLabelFilter
            ->method('hasMatchingLabel')
            ->with(['other-label'])
            ->willReturn(false);

        $parser = $this->createParser();
        $request = $this->createValidRequestWithoutLabels('comment_created');

        $this->expectException(RejectWebhookException::class);
        $this->expectExceptionMessage('Issue does not have any matching labels.');

        $parser->parse($request, self::WEBHOOK_SECRET);
    }

    #[Test]
    public function testWebhookWithMissingLabelsAndApiFetchFailsThrowsRejectException(): void
    {
        $this->issueRepository
            ->expects(self::once())
            ->method('getFull')
            ->with('TEST-123', [], false)
            ->willThrowException(new JiraException('API error'));

        $this->webhookLabelFilter
            ->method('hasMatchingLabel')
            ->with([])
            ->willReturn(false);

        $parser = $this->createParser();
        $request = $this->createValidRequestWithoutLabels('comment_created');

        $this->expectException(RejectWebhookException::class);
        $this->expectExceptionMessage('Issue does not have any matching labels.');

        $parser->parse($request, self::WEBHOOK_SECRET);
    }

    #[Test]
    public function testWebhookWithEmptyLabelsAndNoDbLabelsThrowsRejectException(): void
    {
        $this->webhookLabelFilter
            ->method('hasMatchingLabel')
            ->with([])
            ->willReturn(false);

        $parser = $this->createParser();
        $request = $this->createValidRequest('jira:issue_updated', []);

        $this->expectException(RejectWebhookException::class);
        $this->expectExceptionMessage('Issue does not have any matching labels.');

        $parser->parse($request, self::WEBHOOK_SECRET);
    }

    #[Test]
    public function testWebhookWithInvalidAuthenticationThrowsRejectException(): void
    {
        $parser = $this->createParser();
        $request = $this->createValidRequest('jira:issue_created', ['from-client']);

        $this->expectException(RejectWebhookException::class);
        $this->expectExceptionMessage('Invalid authentication token.');

        $parser->parse($request, 'wrong-secret');
    }

    private function createParser(): JiraRequestParser
    {
        $parser = new JiraRequestParser(
            dispatcher: $this->dispatcher,
            commandBus: $this->commandBus,
            issueRepository: $this->issueRepository,
            webhookLabelFilter: $this->webhookLabelFilter,
        );
        $logger = $this->createMock(LoggerInterface::class);
        $parser->setLogger($logger);

        return $parser;
    }

    private function createValidRequest(string $webhookEvent, array $labels): Request
    {
        $payload = [
            'webhookEvent' => $webhookEvent,
            'issue' => [
                'key' => 'TEST-123',
                'id' => '12345',
                'fields' => [
                    'summary' => 'Test Issue',
                    'labels' => $labels,
                    'project' => [
                        'id' => '1',
                        'key' => 'TEST',
                    ],
                ],
            ],
        ];

        $content = json_encode($payload);
        $signature = sprintf('sha256=%s', hash_hmac('sha256', $content, self::WEBHOOK_SECRET));

        return new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_METHOD' => 'POST',
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE' => $signature,
            ],
            $content
        );
    }

    private function createValidRequestWithoutLabels(string $webhookEvent): Request
    {
        $payload = [
            'webhookEvent' => $webhookEvent,
            'issue' => [
                'key' => 'TEST-123',
                'id' => '12345',
                'fields' => [
                    'summary' => 'Test Issue',
                    'project' => [
                        'id' => '1',
                        'key' => 'TEST',
                    ],
                ],
            ],
        ];

        $content = json_encode($payload);
        $signature = sprintf('sha256=%s', hash_hmac('sha256', $content, self::WEBHOOK_SECRET));

        return new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_METHOD' => 'POST',
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE' => $signature,
            ],
            $content
        );
    }
}
