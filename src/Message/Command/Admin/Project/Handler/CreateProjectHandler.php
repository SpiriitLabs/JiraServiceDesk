<?php

namespace App\Message\Command\Admin\Project\Handler;

use App\Entity\Project;
use App\Message\Command\Admin\Project\CreateProject;
use App\Repository\Jira\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateProjectHandler
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CreateProject $command): ?Project
    {
        $jiraProject = $this->projectRepository->get($command->jiraKey);

        if ($jiraProject === null) {
            return null;
        }

        $avatarsUrlsArray = get_object_vars($jiraProject->avatarUrls);
        $project = new Project(
            name: $jiraProject->name,
            jiraId: $jiraProject->id,
            jiraKey: $jiraProject->key,
            avatarUrl: array_shift($avatarsUrlsArray)
        );

        foreach ($command->users as $user) {
            $project->addUser($user);
        }

        $this->entityManager->persist($project);

        return $project;
    }
}
