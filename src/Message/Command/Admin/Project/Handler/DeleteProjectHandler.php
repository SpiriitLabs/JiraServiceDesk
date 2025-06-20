<?php

namespace App\Message\Command\Admin\Project\Handler;

use App\Controller\App\Project\AbstractController;
use App\Controller\Common\DeleteControllerTrait;
use App\Entity\Project;
use App\Message\Command\Admin\Project\DeleteProject;
use App\Message\Command\Common\DeleteEntity;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class DeleteProjectHandler extends AbstractController
{
    use DeleteControllerTrait;

    public function __construct(
        private readonly FavoriteRepository $favoriteRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(DeleteProject $command): void
    {
        $project = $command->project;

        $projectFavorites = $this->favoriteRepository->findBy([
            'project' => $project,
        ]);
        foreach ($projectFavorites as $projectFavorite) {
            $this->entityManager->remove($projectFavorite);
        }

        $this->handle(
            new DeleteEntity(
                class: Project::class,
                id: $project->getId(),
            ),
        );
    }
}
