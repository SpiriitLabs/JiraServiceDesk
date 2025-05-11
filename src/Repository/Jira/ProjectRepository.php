<?php

namespace App\Repository\Jira;

use JiraCloud\JiraException;
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
        try {
            return $this->service->get($key);
        } catch (JiraException $jiraException) {
            dd($jiraException);

            return null;
        }
    }
}
