<?php

namespace App\Message\Command\Common;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class EmailNotification
{
    public function __construct(
        public User $user,
        public TemplatedEmail $email,
    ) {
    }
}
