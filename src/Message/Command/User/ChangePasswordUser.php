<?php

declare(strict_types=1);

namespace App\Message\Command\User;

use App\Entity\User;

class ChangePasswordUser
{
    public function __construct(
        public readonly User $user,
        public ?string $currentPlainPassword = null,
        public ?string $newPlainPassword = null,
    ) {
    }
}
