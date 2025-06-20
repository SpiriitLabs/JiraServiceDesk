<?php

namespace App\Message\Command\App\Favorite;

use App\Entity\User;

class AbstractFavoriteDTO
{
    public function __construct(
        public string $code,
        public int $projectId,
        public User $user,
    ) {
    }
}
