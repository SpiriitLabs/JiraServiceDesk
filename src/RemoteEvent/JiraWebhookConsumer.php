<?php

namespace App\RemoteEvent;

use App\Controller\Traits\ExceptionCatcherTrait;
use App\Message\Event\Webhook\Comment\CommentCreated;
use App\Message\Event\Webhook\Comment\CommentUpdated;
use App\Message\Event\Webhook\Issue\IssueCreated;
use App\Message\Event\Webhook\Issue\IssueDeleted;
use App\Message\Event\Webhook\Issue\IssueUpdated;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('jira')]
final class JiraWebhookConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use ExceptionCatcherTrait;
    use LoggerAwareTrait;

    public function __construct(
        private readonly MessageBusInterface $commandBus,
    ) {
    }

    public function consume(RemoteEvent $event): void
    {
        if (! $this->support($event)) {
            return;
        }

        $this->commandBus->dispatch($event);
    }

    private function support(RemoteEvent $event): bool
    {
        return match (true) {
            is_a($event, IssueCreated::class)
            || is_a($event, IssueUpdated::class)
            || is_a($event, IssueDeleted::class)
            || is_a($event, CommentCreated::class)
            || is_a($event, CommentUpdated::class) => true,
            default => false,
        };
    }
}
