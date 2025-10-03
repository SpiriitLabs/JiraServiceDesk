<?php

namespace App\Message\Command\Common\Handler;

use App\Message\Command\Common\Notification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class NotificationHandler
{
    public function __construct(
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

        $this->mailer->send(
            $command->email,
        );
    }
}
