<?php

namespace App\Message\Command\App\Favorite;

class AbstractFavoriteDTO
{
    public function __construct(
        public string $code,
    ) {
    }
}
