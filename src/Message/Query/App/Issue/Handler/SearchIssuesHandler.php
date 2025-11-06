<?php

declare(strict_types=1);

namespace App\Message\Query\App\Issue\Handler;

use App\Entity\Project;
use App\Message\Query\App\Issue\SearchIssues;
use App\Model\SearchIssuesResult;
use JiraCloud\Issue\IssueService;
use JiraCloud\Issue\JqlQuery;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SearchIssuesHandler
{
    private IssueService $service;

    public function __construct(
        #[Autowire(env: 'JIRA_ACCOUNT_ID')]
        public string $jiraAccountId,
    ) {
        $this->service = new IssueService();
    }

    public function __invoke(SearchIssues $query): SearchIssuesResult
    {
        $jql = new JqlQuery()
            ->addInExpression('labels', [$query->user->getJiraLabel()])
        ;

        if (count($query->filter->projects) === 0 && $query->user !== null) {
            $query->filter->projects = $query->user->getProjects()
                ->toArray()
            ;
        }

        if ($query->filter !== null && count($query->filter->projects) === 0) {
            return new SearchIssuesResult(
                total: 0,
                issues: [],
            );
        }

        if ($query->filter !== null && count($query->filter->projects) !== 0) {
            $jql->addInExpression(
                field: JqlQuery::FIELD_PROJECT,
                values: array_map(
                    fn (Project $project) => $project->jiraKey,
                    $query->filter->projects,
                )
            );
        }

        if ($query->onlyUserAssigned) {
            $jql
                ->setAssignee($this->jiraAccountId)
            ;
        }

        if ($query->filter !== null && $query->filter->assigneeIds !== null && count(
            $query->filter->assigneeIds
        ) !== 0) {
            $assigneeJqlQuery = new JqlQuery();
            foreach ($query->filter->assigneeIds as $assigneeId) {
                $assigneeJqlQuery
                    ->addExpression(JqlQuery::FIELD_ASSIGNEE, '=', $assigneeId, JqlQuery::KEYWORD_OR)
                ;
            }

            $jql
                ->addAnyExpression('and (' . $assigneeJqlQuery->getQuery() . ')')
            ;
        }

        if ($query->filter !== null && $query->filter->statusesIds !== null) {
            if (count($query->filter->statusesIds) === 0) {
                return new SearchIssuesResult(total: 0);
            }

            $jql->addInExpression('status', $query->filter->statusesIds);
        }

        if ($query->filter !== null && $query->filter->hasResolvedMasked == true) {
            $jql->addIsNullExpression(JqlQuery::FIELD_RESOLVED);
        }

        if ($query->filter !== null && $query->filter->query !== null) {
            $filterJqlQuery = new JqlQuery();
            $filterJqlQuery
                ->addExpression('text', '~', $query->filter->query, '')
                ->addExpression('summary', '~', $query->filter->query, JqlQuery::KEYWORD_OR)
                ->addExpression('textfields', '~', $query->filter->query, JqlQuery::KEYWORD_OR)
            ;

            if (preg_match('/^[A-Z0-9]+-\d+$/i', $query->filter->query)) {
                $filterJqlQuery = new JqlQuery();
                $filterJqlQuery
                    ->addExpression('issuekey', '=', strtoupper($query->filter->query), JqlQuery::KEYWORD_OR)
                ;
            }

            $jql
                ->addAnyExpression('and (' . $filterJqlQuery->getQuery() . ')')
            ;
        }
        $jql->addAnyExpression(sprintf('%s %s %s', JqlQuery::KEYWORD_ORDER_BY, $query->sort->by, $query->sort->dir));

        $issuesFields = [
            'id',
            'key',
            'summary',
            'assignee',
            'priority',
            'status',
            'timeoriginalestimate',
            'created',
            'updated',
            'project',
        ];
        $issues = $this->service->search(
            jql: $jql->getQuery(),
            nextPageToken: $query->pageToken ?? '',
            maxResults: $query->maxIssuesResults,
            fields: $issuesFields,
        );

        return new SearchIssuesResult(
            total: count($issues->issues),
            issues: $issues->getIssues(),
            nextPageToken: $issues->nextPageToken,
        );
    }
}
