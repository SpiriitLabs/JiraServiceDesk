<?php

namespace App\Message\Command\App\Favorite;

use App\Entity\User;

class DeleteFavorite extends AbstractFavoriteDTO
{
    public function __construct(
        string $code,
        int $projectId,
        User $user,
    ) {
        parent::__construct($code, $projectId, $user);
    }
}
