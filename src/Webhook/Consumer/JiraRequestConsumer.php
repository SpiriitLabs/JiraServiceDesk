<?php

namespace App\Webhook\Consumer;

use App\Controller\Traits\ExceptionCatcherTrait;
use App\Message\Event\Webhook\Comment\CommentCreated;
use App\Message\Event\Webhook\Comment\CommentUpdated;
use App\Message\Event\Webhook\Issue\IssueCreated;
use App\Message\Event\Webhook\Issue\IssueDeleted;
use App\Message\Event\Webhook\Issue\IssueUpdated;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('jira')]
class JiraRequestConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use ExceptionCatcherTrait;

    // 1 minute = 300_000 ms
    // 30 secondes = 30_000 ms
    private const DELAY_STAMP = 300_000;

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        #[Autowire(env: 'APP_ENV')]
        private readonly string $APP_ENV,
    ) {
    }

    public function consume(RemoteEvent $event): void
    {
        if (! $this->support($event)) {
            return;
        }

        $this->commandBus->dispatch(
            $event,
            [
                new DelayStamp(self::DELAY_STAMP),
            ]
        );
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
