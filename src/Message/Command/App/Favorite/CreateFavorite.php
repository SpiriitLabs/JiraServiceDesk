<?php

namespace App\Message\Command\App\Favorite;

use App\Entity\User;

class CreateFavorite extends AbstractFavoriteDTO
{
    public function __construct(
        string $code,
        public string $name,
        public string $link,
        public User $user,
    ) {
        parent::__construct($code);
    }
}
