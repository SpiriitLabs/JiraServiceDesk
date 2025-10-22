<?php

declare(strict_types=1);

namespace App\Message\Query\App\Project\Handler;

use App\Message\Query\App\Project\GetProjectRolesByJiraKey;
use App\Repository\Jira\ProjectRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetProjectRolesByJiraKeyHandler
{
    public function __construct(
        private ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(GetProjectRolesByJiraKey $query): array
    {
        $jiraRoles = $this->projectRepository->getRoles($query->jiraKey);

        foreach ($jiraRoles as $jiraRoleName => $jiraRoleSelf) {
            $jiraRoleSelfArray = explode('/', rtrim($jiraRoleSelf, '/'));
            $jiraRoles[$jiraRoleName] = end($jiraRoleSelfArray);
        }

        return $jiraRoles;
    }
}
