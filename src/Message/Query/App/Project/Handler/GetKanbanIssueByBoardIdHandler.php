<?php

namespace App\Message\Query\App\Project\Handler;

use App\Formatter\Jira\IssueKanbanFormatter;
use App\Message\Query\App\Project\GetKanbanIssueByBoardId;
use App\Repository\Jira\BoardRepository;
use JiraCloud\Issue\JqlQuery;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetKanbanIssueByBoardIdHandler
{
    public function __construct(
        private IssueKanbanFormatter $formatter,
        private BoardRepository $boardRepository,
    ) {
    }

    public function __invoke(GetKanbanIssueByBoardId $query): array
    {
        $boardColumnConfiguration = $this->boardRepository->getBoardConfigurationById(
            id: $query->boardId
        );

        $boardIssues = $this->boardRepository->getBoardIssuesById(
            id: $query->boardId,
            parameters: [
                'maxResults' => 500,
                'jql' => new JqlQuery()
                    ->addInExpression(JqlQuery::FIELD_LABELS, ['from-client'])->getQuery(),
                'fields' => [
                    'description',
                    'id',
                    'key',
                    'flagged',
                    'assignee',
                    'status',
                    'priority',
                    'summary',
                    'created',
                    'updated',
                    'timeestimate',
                ],
                'expand' => 'transitions',
            ],
        );

        return $this->formatter->format($boardIssues, $query->project, $boardColumnConfiguration);
    }
}
