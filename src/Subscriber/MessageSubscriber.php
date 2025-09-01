<?php

namespace App\Subscriber;

use App\Enum\LogEntry\LogType;
use App\Message\Command\App\LogEntry\CreateLogEntry;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;

class MessageSubscriber implements EventSubscriberInterface
{
    use HandleTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        MessageBusInterface $commandBus,
    ) {
        $this->messageBus = $commandBus;
    }

    public function onMessage(MessageEvent $event): void
    {
        if (! $event->isQueued()) {
            return;
        }
        $message = $event->getMessage();
        if (! $message instanceof Email) {
            return;
        }
        $to = implode(', ', array_map(fn ($a) => $a->getAddress(), $message->getTo()));
        $this->logger->info('Email envoyé', [
            'to' => $to,
            'subject' => $message->getSubject(),
        ]);
        $this->handle(
            new CreateLogEntry(
                logType: LogType::EMAIL,
                subject: 'Email envoyé',
                datas: [
                    'to' => $to,
                    'subject' => $message->getSubject(),
                ],
            )
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }
}
