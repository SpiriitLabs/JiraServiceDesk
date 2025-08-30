<?php

namespace App\Subscriber;

use App\Entity\EmailLog;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;

class MessageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
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
        $emailLog = new EmailLog(
            recipient: $to,
            subject: $message->getSubject(),
        );
        $this->entityManager->persist($emailLog);
        $this->entityManager->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }
}
