<?php

namespace App\Message\Command\App\Favorite\Handler;

use App\Message\Command\App\Favorite\DeleteFavorite;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class DeleteFavoriteHandler
{
    public function __construct(
        private FavoriteRepository $favoriteRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(DeleteFavorite $command): void
    {
        $favorite = $this->favoriteRepository->findOneBy(
            [
                'code' => $command->code,
                'user' => $command->user,
            ],
        );

        if ($favorite === null) {
            return;
        }

        $this->entityManager->remove($favorite);
    }
}
