<?php

declare(strict_types=1);

namespace App\Message\Command\Common\Handler;

use App\Entity\Notification as NotificationEntity;
use App\Enum\LogEntry\Type;
use App\Enum\Notification\NotificationChannel;
use App\Message\Command\Common\Notification;
use App\Service\SlackBlockKitBuilder;
use App\Service\SlackNotificationService;
use App\Subscriber\Event\NotificationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class NotificationHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly SlackNotificationService $slackNotificationService,
        private readonly SlackBlockKitBuilder $slackBlockKitBuilder,
    ) {
    }

    public function __invoke(Notification $command): void
    {
        if ($command->user->enabled === false) {
            return;
        }

        if ($command->channels === []) {
            return;
        }

        $channelValues = array_map(
            static fn (NotificationChannel $c): string => $c->value,
            $command->channels,
        );
        $this->logger?->info('NotificationHandler - Processing notification', [
            'user' => $command->user->email,
            'channels' => $channelValues,
            'link' => $command->link,
        ]);

        if (in_array(NotificationChannel::EMAIL, $command->channels, true) && $command->email) {
            $this->mailer->send(
                $command->email,
            );

            $this->dispatcher->dispatch(
                new NotificationEvent(
                    user: $command->user,
                    message: sprintf('Notification email sent to "%s"', $command->user->email),
                    type: Type::EMAIL,
                    extraData: [
                        'subject' => $command->subject,
                        'body' => $command->body,
                        'link' => $command->link,
                    ],
                ),
                NotificationEvent::EVENT_NAME,
            );
        }

        if (in_array(NotificationChannel::SLACK, $command->channels, true)) {
            $blocks = $this->slackBlockKitBuilder->build(
                notificationType: $command->notificationType,
                subject: $command->subject,
                body: $command->body,
                link: $command->link,
                locale: $command->user->preferredLocale->value,
                extraContext: $command->slackExtraContext,
            );

            $this->slackNotificationService->sendDirectMessage(
                user: $command->user,
                text: $command->subject,
                blocks: $blocks,
            );

            $this->dispatcher->dispatch(
                new NotificationEvent(
                    user: $command->user,
                    message: sprintf('Slack notification sent to "%s"', $command->user->email),
                    type: Type::SLACK,
                    extraData: [
                        'subject' => $command->subject,
                        'body' => $command->body,
                        'link' => $command->link,
                    ],
                ),
                NotificationEvent::EVENT_NAME,
            );
        }

        if (in_array(NotificationChannel::IN_APP, $command->channels, true)) {
            // Fix subject too long.
            $subject = substr($command->subject, 0, 255);

            $notification = new NotificationEntity(
                notificationType: $command->notificationType,
                subject: $subject,
                body: $command->body,
                link: $command->link,
                user: $command->user,
            );

            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }
    }
}
