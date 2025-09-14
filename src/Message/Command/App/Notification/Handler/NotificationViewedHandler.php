<?php

namespace App\Message\Command\App\Notification\Handler;

use App\Entity\Notification;
use App\Message\Command\App\Notification\NotificationViewed;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class NotificationViewedHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(NotificationViewed $command): void
    {
        $entity = $this->entityManager->find(Notification::class, $command->notificationId);

        $entity->isViewed = true;
        $this->entityManager->flush();
    }
}
