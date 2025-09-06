<?php

namespace App\Message\Command\App\Notification\Handler;

use App\Entity\Notification;
use App\Message\Command\App\Notification\CreateNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateNotificationHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CreateNotification $command): ?Notification
    {
        $notification = new Notification(
            notificationType: $command->notificationType,
            subject: $command->subject,
            body: $command->body,
            link: $command->link,
            user: $command->user,
        );

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }
}
