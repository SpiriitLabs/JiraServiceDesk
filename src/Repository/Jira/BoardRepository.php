<?php

namespace App\Repository\Jira;

use App\Entity\Project;
use JiraCloud\Board\Board;
use JiraCloud\Board\BoardService;
use JiraCloud\Issue\Issue;
use JiraCloud\JiraException;

class BoardRepository
{

    private BoardService $service;

    public function __construct() {
        $this->service = new BoardService();
    }

    /**
     * @return array<Board>
     */
    public function getBoardByProject(Project $project): array
    {
        try {
            return $this->service->getBoardList(paramArray: ['projectKeyOrId' => $project->jiraId])->getArrayCopy();
        } catch (JiraException $jiraException) {
            return [];
        }
    }

    public function getBoardById(string $id): ?Board
    {
        try {
            return $this->service->getBoard($id);
        } catch (JiraException $jiraException) {
            return null;
        }
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getBoardIssuesById(string $id, array $parameters = []): array
    {
        try {
            return $this->service->getBoardIssues($id, $parameters)->getArrayCopy();
        } catch (JiraException $jiraException) {
            return [];
        }
    }

}
