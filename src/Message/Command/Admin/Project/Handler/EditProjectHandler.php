<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\Project\Handler;

use App\Entity\Project;
use App\Message\Command\Admin\Project\EditProject;
use App\Message\Command\Admin\Project\GenerateProjectIssueTypes;
use App\Repository\Jira\ProjectRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
readonly class EditProjectHandler
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private MessageBusInterface $commandBus,
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
        $project->jiraId = (int) $jiraProject->id;
        $project->assignableRolesIds = $command->assignableRolesIds;
        $project->backlogStatusesIds = $command->backlogStatusesIds;
        $project->defaultIssueType = $command->defaultIssueType;

        foreach ($project->getUsers() as $projectUser) {
            $project->removeUser($projectUser);
        }
        foreach ($command->users as $user) {
            $project->addUser($user);
        }

        $this->commandBus->dispatch(
            new GenerateProjectIssueTypes(
                project: $project,
            ),
        );

        $cache = new FilesystemAdapter();
        $cache->clear(sprintf('jira.assignable_users_%s', $project->jiraKey));
        $cache->clear(sprintf('jira.kanban_assignable_users_list_%s', $project->jiraKey));

        return $project;
    }
}
