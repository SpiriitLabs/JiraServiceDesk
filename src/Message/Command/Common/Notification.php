<?php

declare(strict_types=1);

namespace App\Message\Command\Common;

use App\Entity\User;
use App\Enum\Notification\NotificationType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class Notification
{
    public function __construct(
        public User $user,
        public ?TemplatedEmail $email = null,
        public NotificationType $notificationType,
        public string $subject,
        public string $body,
        public string $link,
    ) {
    }
}
