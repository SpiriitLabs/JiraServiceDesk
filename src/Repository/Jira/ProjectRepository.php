<?php

declare(strict_types=1);

namespace App\Repository\Jira;

use JiraCloud\Issue\IssueType;
use JiraCloud\Project\Project;
use JiraCloud\Project\ProjectService;

class ProjectRepository
{
    private ProjectService $service;

    public function __construct()
    {
        $this->service = new ProjectService();
    }

    public function get(string $key): ?Project
    {
        return $this->service->get($key);
    }

    public function getRoles(string $key): array
    {
        return $this->service->getProjectRoles($key);
    }

    /**
     * @return IssueType[]
     */
    public function getStatuses(string $key): array
    {
        return $this->service->getStatuses($key);
    }
}
