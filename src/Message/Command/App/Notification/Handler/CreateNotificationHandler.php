<?php

namespace App\Message\Command\App\Notification\Handler;

use App\Entity\Notification;
use App\Message\Command\App\Notification\CreateNotification;
use App\Subscriber\Event\NotificationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
readonly class CreateNotificationHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function __invoke(CreateNotification $command): ?Notification
    {
        if ($command->user->enabled === false) {
            return null;
        }

        if ($command->email) {
            $this->mailer->send(
                $command->email,
            );

            $this->dispatcher->dispatch(
                new NotificationEvent(
                    user: $command->user,
                    message: sprintf('Notification email sent to "%s"', $command->user->email),
                    extraData: [
                        'subject' => $command->subject,
                        'body' => $command->body,
                        'link' => $command->link,
                    ],
                ),
                NotificationEvent::EVENT_NAME,
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

        return $notification;
    }
}
