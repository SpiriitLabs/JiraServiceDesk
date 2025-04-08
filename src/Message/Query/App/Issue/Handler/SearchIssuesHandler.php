<?php

namespace App\Message\Query\App\Issue\Handler;

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

        foreach ($query->user->getProjects() as $userProject) {
            $jql->setProject($userProject->jiraKey);
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
            startAt: ($query->page - 1) * SearchIssues::MAX_ISSUES_RESULTS,
            maxResults: SearchIssues::MAX_ISSUES_RESULTS,
            fields: $issues_fields,
        );

        $page = $issues->getTotal() / SearchIssues::MAX_ISSUES_RESULTS;

        return new SearchIssuesResult(
            page: $page,
            total: $issues->getTotal(),
            issues: $issues->getIssues(),
        );
    }
}
