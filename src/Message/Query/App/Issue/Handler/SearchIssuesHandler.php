<?php

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
            ->addInExpression('labels', ['from-client'])
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

        if ($query->filter !== null && $query->filter->query !== null) {
            $jql
                ->addExpression('text', '~', $query->filter->query)
                ->addExpression('summary', '~', $query->filter->query)
            ;
        }

        $issues_fields = [
            'id',
            'key',
            'summary',
            'assignee',
            'priority',
            'status',
            'timeoriginalestimate',
            'created',
        ];
        $jql->addAnyExpression(sprintf('%s %s %s', JqlQuery::KEYWORD_ORDER_BY, $query->sort->by, $query->sort->dir));
        $issues = $this->service->search(
            jql: $jql->getQuery(),
            nextPageToken: $query->pageToken ?? '',
            maxResults: $query->maxIssuesResults,
            fields: $issues_fields,
        );

        return new SearchIssuesResult(
            total: count($issues->issues),
            issues: $issues->getIssues(),
            nextPageToken: $issues->nextPageToken,
        );
    }
}
