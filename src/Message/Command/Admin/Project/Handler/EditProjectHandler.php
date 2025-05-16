<?php

namespace App\Message\Command\Admin\Project\Handler;

use App\Entity\Project;
use App\Message\Command\Admin\Project\EditProject;
use App\Repository\Jira\ProjectRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class EditProjectHandler
{
    public function __construct(
        private ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(EditProject $command): ?Project
    {
        $project = $command->project;
        $jiraProject = $this->projectRepository->get($command->jiraKey);

        if ($jiraProject === null) {
            return null;
        }

        $project->name = $jiraProject->name;
        $project->jiraId = $jiraProject->id;
        $project->assignableRolesIds = $command->assignableRolesIds;
        $project->backlogStatuses = $command->backlogStatuses;

        foreach ($project->getUsers() as $projectUser) {
            $project->removeUser($projectUser);
        }
        foreach ($command->users as $user) {
            $project->addUser($user);
        }

        return $project;
    }
}
