<?php

namespace App\Repository\Jira;

use App\Entity\Project;
use JiraCloud\Issue\Reporter;
use JiraCloud\JiraException;
use JiraCloud\Project\ProjectService;
use JiraCloud\User\UserService;

class UserRepository
{
    private UserService $service;

    private ProjectService $projectService;

    public function __construct()
    {
        $this->service = new UserService();
        $this->projectService = new ProjectService();
    }

    /**
     * @return Reporter[]
     */
    public function getAssignableUser(Project $project): array
    {
        try {
            $rolesActors = [];

            foreach ($project->assignableRolesIds as $roleCanBeAssignable) {
                $rolesActors = array_merge(
                    $rolesActors,
                    $this->projectService->getProjectRole(
                        projectIdOrKey: $project->jiraKey,
                        roleId: $roleCanBeAssignable,
                        excludeInactiveUsers: true
                    )
                        ->actors,
                );
            }

            return $rolesActors;
        } catch (JiraException $e) {
            return [];
        }
    }
}
