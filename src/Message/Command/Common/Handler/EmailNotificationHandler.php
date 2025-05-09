<?php

namespace App\Message\Command\Common\Handler;

use App\Message\Command\Common\EmailNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EmailNotificationHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {
    }

    public function __invoke(EmailNotification $command): void
    {
        $this->mailer->send(
            $command->email,
        );
    }
}
