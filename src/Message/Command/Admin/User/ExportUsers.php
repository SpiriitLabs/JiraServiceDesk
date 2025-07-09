<?php

namespace App\Message\Command\Admin\User;

use App\Entity\User;

class ExportUsers
{
    public function __construct(
        public User $user,
    ) {
    }
}
