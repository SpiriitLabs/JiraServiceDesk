<?php

namespace App\Message\Command\Common\Handler;

use App\Entity\Notification as NotificationEntity;
use App\Message\Command\Common\Notification;
use App\Subscriber\Event\NotificationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
readonly class NotificationHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private EventDispatcherInterface $dispatcher,
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

        // Reduce notif creation.
        $latestsNotifications = $this
            ->entityManager
            ->getRepository(NotificationEntity::class)
            ->findBy([
                'link' => $command->link,
                'user' => $command->user,
            ])
        ;
        $latestsNotifications = array_filter($latestsNotifications, function (NotificationEntity $notification): bool {
            return $notification->getSendAt()
                ->getTimestamp() > (time() - 300)
            ;
        });
        if (! empty($latestsNotifications)) {
            return;
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
    }
}
