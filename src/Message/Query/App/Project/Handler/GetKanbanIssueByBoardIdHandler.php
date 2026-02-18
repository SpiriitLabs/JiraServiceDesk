<?php

declare(strict_types=1);

namespace App\Message\Query\App\Project\Handler;

use App\Formatter\Jira\IssueKanbanFormatter;
use App\Message\Query\App\Project\GetKanbanIssueByBoardId;
use App\Model\SortParams;
use App\Repository\Jira\BoardRepository;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\JqlQuery;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetKanbanIssueByBoardIdHandler
{
    public function __construct(
        private IssueKanbanFormatter $formatter,
        private BoardRepository $boardRepository,
        #[Autowire(env: 'RESOLUTIONDATE_MAX_TO_KEEP')]
        private string $resolutionDate,
    ) {
    }

    /**
     * @return Issue[]
     */
    public function __invoke(GetKanbanIssueByBoardId $query): array
    {
        $boardColumnConfiguration = $this->boardRepository->getBoardConfigurationById(
            id: $query->boardId
        );

        $subQuery = new JqlQuery();
        $subQuery
            ->addExpression('resolutiondate', '>=', $this->resolutionDate, JqlQuery::KEYWORD_OR)
            ->addIsNullExpression('resolutiondate', JqlQuery::KEYWORD_OR)
        ;

        $jql = new JqlQuery()
            ->addInExpression(JqlQuery::FIELD_LABELS, $query->user->getJiraLabels())
            ->addAnyExpression('and (' . $subQuery->getQuery() . ')')
        ;

        if ($query->assigneeId !== '') {
            $jql->addInExpression('assignee', [$query->assigneeId]);
        }

        if (count($query->priorityJiraIds) > 0) {
            $jql->addInExpression('priority', $query->priorityJiraIds, needQuote: false);
        }

        if ($query->sort !== null && $query->sort !== '') {
            $sort = SortParams::createSort($query->sort);
            $jql->addAnyExpression(sprintf('%s %s %s', JqlQuery::KEYWORD_ORDER_BY, $sort->by, $sort->dir));
        }

        // dd($jql->getQuery());
        $boardIssues = $this->boardRepository->getBoardIssuesById(
            id: $query->boardId,
            parameters: [
                'maxResults' => 500,
                'jql' => $jql->getQuery(),
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
                    'timeoriginalestimate',
                ],
                'expand' => 'transitions',
            ],
        );

        return $this->formatter->format($boardIssues, $query->project, $boardColumnConfiguration);
    }
}
