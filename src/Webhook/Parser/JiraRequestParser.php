<?php

declare(strict_types=1);

namespace App\Webhook\Parser;

use App\Enum\LogEntry\Type;
use App\Message\Event\Webhook\Comment\CommentCreated;
use App\Message\Event\Webhook\Comment\CommentUpdated;
use App\Message\Event\Webhook\Issue\IssueCreated;
use App\Message\Event\Webhook\Issue\IssueDeleted;
use App\Message\Event\Webhook\Issue\IssueUpdated;
use App\Repository\Jira\IssueRepository;
use App\Subscriber\Event\NotificationEvent;
use App\Webhook\WebhookLabelFilter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Webhook\Client\AbstractRequestParser;
use Symfony\Component\Webhook\Exception\RejectWebhookException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class JiraRequestParser extends AbstractRequestParser implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly MessageBusInterface $commandBus,
        private readonly IssueRepository $issueRepository,
        private readonly WebhookLabelFilter $webhookLabelFilter,
    ) {
    }

    protected function getRequestMatcher(): RequestMatcherInterface
    {
        return new ChainRequestMatcher([
            new MethodRequestMatcher(Request::METHOD_POST),
            new IsJsonRequestMatcher(),
        ]);
    }

    protected function doParse(Request $request, #[\SensitiveParameter] string $secret): ?RemoteEvent
    {
        if (
            hash_equals(
                $request->headers->get('x-hub-signature'),
                sprintf(
                    'sha256=%s',
                    hash_hmac('sha256', $request->getContent(), $secret)
                )
            ) == false
        ) {
            throw new RejectWebhookException(Response::HTTP_UNAUTHORIZED, 'Invalid authentication token.');
        }

        $payload = $request->getPayload();
        $this->logger->info('WEBHOOK', [
            'payload' => $payload->all(),
        ]);
        if ($payload->has('webhookEvent') == false) {
            throw new RejectWebhookException(
                Response::HTTP_BAD_REQUEST,
                'Request payload does not contain required fields.'
            );
        }

        $issueLabels = $this->getIssueLabels($payload->all());
        if ($this->webhookLabelFilter->hasMatchingLabel($issueLabels) === false) {
            throw new RejectWebhookException(Response::HTTP_OK, 'Issue does not have any matching labels.');
        }

        $this->logger->info('WEBHOOK', [
            'event' => $payload->get('webhookEvent'),
        ]);

        $event = match ($payload->get('webhookEvent')) {
            'jira:issue_created' => new IssueCreated(payload: $payload->all()),
            'jira:issue_updated' => new IssueUpdated(payload: $payload->all()),
            'jira:issue_deleted' => new IssueDeleted(payload: $payload->all()),
            'comment_created' => new CommentCreated(payload: $payload->all()),
            'comment_updated' => new CommentUpdated(payload: $payload->all()),
            default => new RejectWebhookException(message: 'Invalid webhook event.'),
        };

        $this->dispatcher->dispatch(
            new NotificationEvent(
                user: null,
                message: sprintf('New webhook successfully for event "%s"', $payload->get('webhookEvent')),
                type: Type::WEBHOOK,
                extraData: [
                    'issue_key' => $payload->all()['issue']['key'],
                ],
            ),
            NotificationEvent::EVENT_NAME,
        );

        return $event;
    }

    /**
     * @return array<string>
     */
    private function getIssueLabels(array $payload): array
    {
        if (isset($payload['issue']['fields']['labels'])) {
            return $payload['issue']['fields']['labels'];
        }

        $issueKey = $payload['issue']['key'] ?? null;
        if ($issueKey === null) {
            return [];
        }

        try {
            $issue = $this->issueRepository->getFull($issueKey, checkLabel: false);

            return $issue->fields->labels ?? [];
        } catch (\Exception $e) {
            $this->logger?->warning('Failed to fetch issue labels from Jira API', [
                'issue_key' => $issueKey,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
