<?php

namespace App\Message\Command\App\Favorite\Handler;

use App\Entity\Favorite;
use App\Message\Command\App\Favorite\CreateFavorite;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateFavoriteHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(CreateFavorite $command): ?Favorite
    {
        $project = $this->projectRepository->findOneBy([
            'id' => $command->projectId,
        ]);

        $favorite = new Favorite(
            name: $command->name,
            code: $command->code,
            link: $command->link,
            user: $command->user,
            project: $project,
        );

        $this->entityManager->persist($favorite);

        return $favorite;
    }
}
