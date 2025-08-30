<?php

namespace App\Subscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;

class MessageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function onMessage(MessageEvent $event): void
    {
        $message = $event->getMessage();
        if (! $message instanceof Email) {
            return;
        }
        $this->logger->info('Email envoyé', [
            'to' => implode(', ', array_map(fn ($a) => $a->getAddress(), $message->getTo())),
            'subject' => $message->getSubject(),
        ]);
    }

    public static function getSubscribedEvents()
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }
}
