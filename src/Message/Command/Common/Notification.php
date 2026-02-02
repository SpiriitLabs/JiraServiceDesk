<?php

declare(strict_types=1);

namespace App\Message\Command\Common;

use App\Entity\User;
use App\Enum\Notification\NotificationChannel;
use App\Enum\Notification\NotificationType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class Notification
{
    /**
     * @param NotificationChannel[] $channels
     * @param array<string, string> $slackExtraContext
     */
    public function __construct(
        public User $user,
        public ?TemplatedEmail $email = null,
        public NotificationType $notificationType,
        public string $subject,
        public string $body,
        public string $link,
        public array $channels = [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
        public array $slackExtraContext = [],
    ) {
    }
}
