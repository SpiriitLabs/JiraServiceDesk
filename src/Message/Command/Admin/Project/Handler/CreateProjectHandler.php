<?php

namespace App\Message\Command\Admin\Project\Handler;

use App\Entity\IssueType;
use App\Entity\Project;
use App\Exception\Project\ProjectAlreadyExistException;
use App\Message\Command\Admin\Project\CreateProject;
use App\Repository\Jira\ProjectRepository;
use App\Repository\ProjectRepository as AppProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateProjectHandler
{
    public function __construct(
        private AppProjectRepository $appProjectRepository,
        private ProjectRepository $jiraProjectRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CreateProject $command): ?Project
    {
        dd($this->appProjectRepository->findOneBy([
            'jiraKey' => $command->jiraKey,
        ]));
        if ($this->appProjectRepository->findOneBy([
            'jiraKey' => $command->jiraKey,
        ]) !== null) {
            throw new ProjectAlreadyExistException();
        }

        $jiraProject = $this->jiraProjectRepository->get($command->jiraKey);
        dd($jiraProject);

        if ($jiraProject === null) {
            return null;
        }

        $project = new Project(
            name: $jiraProject->name,
            jiraId: $jiraProject->id,
            jiraKey: $jiraProject->key,
            description: $jiraProject->description,
        );

        foreach ($command->users as $user) {
            $project->addUser($user);
        }

        foreach ($jiraProject->issueTypes as $issueType) {
            $issueType = new IssueType(
                jiraId: $issueType->id,
                name: $issueType->name,
                description: $issueType->description,
                iconUrl: $issueType->iconUrl,
            );

            $this->entityManager->persist($issueType);
            $project = $project->addIssuesType($issueType);
        }

        $this->entityManager->persist($project);

        return $project;
    }
}
