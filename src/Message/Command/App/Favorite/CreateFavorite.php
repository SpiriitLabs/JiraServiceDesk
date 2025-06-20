<?php

namespace App\Message\Command\App\Favorite;

use App\Entity\User;

class CreateFavorite extends AbstractFavoriteDTO
{
    public function __construct(
        string $code,
        int $projectId,
        User $user,
        public string $name,
        public string $link,
    ) {
        parent::__construct($code, $projectId, $user);
    }
}
