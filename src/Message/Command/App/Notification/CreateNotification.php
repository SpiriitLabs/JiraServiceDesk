<?php

namespace App\Message\Command\App\Notification;

use App\Entity\User;
use App\Enum\Notification\NotificationType;

class CreateNotification
{
    public function __construct(
        public NotificationType $notificationType,
        public string $subject,
        public string $body,
        public string $link,
        public User $user
    ) {
    }
}
