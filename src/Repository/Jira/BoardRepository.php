<?php

namespace App\Repository\Jira;

use App\Entity\Project;
use JiraCloud\Board\Board;
use JiraCloud\Board\BoardColumnConfig;
use JiraCloud\Board\BoardService;
use JiraCloud\Issue\AgileIssue;
use JiraCloud\JiraException;

class BoardRepository
{
    private BoardService $service;

    public function __construct()
    {
        $this->service = new BoardService();
    }

    /**
     * @return array<Board>
     */
    public function getBoardByProject(Project $project): array
    {
        try {
            return $this->service->getBoardList(paramArray: [
                'projectKeyOrId' => $project->jiraId,
            ])->getArrayCopy();
        } catch (JiraException $jiraException) {
            return [];
        }
    }

    /**
     * @return AgileIssue[]
     */
    public function getBoardIssuesById(string $id, array $parameters = []): array
    {
        try {
            $issues = $this->service->getBoardIssues($id, $parameters);
            if ($issues == null) {
                return [];
            }

            return $issues->getArrayCopy();
        } catch (JiraException $jiraException) {
            return [];
        }
    }

    public function getBoardConfigurationById(string $id, array $parameters = []): ?BoardColumnConfig
    {
        return $this->service->getBoardColumnConfiguration($id, $parameters);
    }
}
