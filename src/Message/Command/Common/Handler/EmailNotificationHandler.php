<?php

namespace App\Message\Command\Common\Handler;

use App\Message\Command\Common\EmailNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class EmailNotificationHandler
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function __invoke(EmailNotification $command): void
    {
        if ($command->user->preferenceNotification === false) {
            return;
        }

        $this->mailer->send(
            $command->email,
        );
    }
}
