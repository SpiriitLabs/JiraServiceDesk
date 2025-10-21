<?php

namespace App\Subscriber;

use App\Message\Command\App\LogEntry\CreateLogEntry;
use App\Subscriber\Event\NotificationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class NotificationEventSubscriber implements EventSubscriberInterface
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $commandBus,
    ) {
        $this->messageBus = $commandBus;
    }

    public function onNotification(NotificationEvent $event): void
    {
        $this->handle(
            new CreateLogEntry(
                type: $event->type,
                subject: $event->message,
                datas: $event->extraData,
                user: $event->user,
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NotificationEvent::EVENT_NAME => 'onNotification',
        ];
    }
}
