<?php

namespace App\Message\Command\App\Favorite\Handler;

use App\Entity\Favorite;
use App\Message\Command\App\Favorite\CreateFavorite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateFavoriteHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CreateFavorite $command): ?Favorite
    {
        $favorite = new Favorite(
            name: $command->name,
            code: $command->code,
            link: $command->link,
            user: $command->user,
        );

        $this->entityManager->persist($favorite);

        return $favorite;
    }
}
