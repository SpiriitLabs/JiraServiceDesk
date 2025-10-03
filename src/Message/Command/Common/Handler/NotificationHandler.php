<?php

namespace App\Message\Command\Common\Handler;

use App\Entity\Notification as NotificationEntity;
use App\Message\Command\Common\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class NotificationHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
    ) {
    }

    public function __invoke(Notification $command): void
    {
        if ($command->user->preferenceNotification === false) {
            return;
        }
        if ($command->user->enabled === false) {
            return;
        }

        if ($command->email) {
            $this->mailer->send(
                $command->email,
            );
        }

        $notification = new NotificationEntity(
            notificationType: $command->notificationType,
            subject: $command->subject,
            body: $command->body,
            link: $command->link,
            user: $command->user,
        );

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
