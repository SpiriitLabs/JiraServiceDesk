<?php

namespace App\Controller\App\Project\Issue;

use App\Controller\Common\GetControllerTrait;
use App\Entity\Project;
use App\Message\Query\App\Issue\SearchIssues;
use App\Model\Filter\IssueFilter;
use App\Model\SearchIssuesResult;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route(
    path: '/project/{projectKey}/issues/backlog',
)]
class ShowBacklogController extends AbstractController
{
    use GetControllerTrait;

    #[Route(
        path: '/list',
        name: RouteCollection::SHOW_BACKLOG_LIST->value,
        methods: [Request::METHOD_GET],
    )]
    public function list(
        #[MapEntity(mapping: [
            'projectKey' => 'jiraKey',
        ])]
        Project $project,
        Request $request,
    ): Response {
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        $page = $request->get('page', null);
        $defaultSort = 'id';
        $sort = $request->get('_sort', $defaultSort);

        $issueFilter = new IssueFilter(
            projects: [$project],
            statusesIds: $project->backlogStatusesIds,
        );
        /** @var SearchIssuesResult $searchIssueResult */
        $searchIssueResult = $this->handle(
            new SearchIssues(
                sort: $sort,
                pageToken: $page,
                filter: $issueFilter,
                maxIssuesResults: ($sort !== $defaultSort ? 1000 : SearchIssues::MAX_ISSUES_RESULTS),
            )
        );

        return $this->renderBlock(
            view: 'app/project/issue/project_view_issues_backlog.stream.html.twig',
            block: 'list',
            parameters: [
                'searchIssuesResult' => $searchIssueResult,
                'nextPage' => $searchIssueResult->nextPageToken,
                'project' => $project,
            ]
        );
    }

    #[Route(
        path: '/list/next',
        name: RouteCollection::SHOW_BACKLOG_LIST_NEXT->value,
        methods: [Request::METHOD_GET],
    )]
    public function streamBacklog(
        #[MapEntity(mapping: [
            'projectKey' => 'jiraKey',
        ])]
        Project $project,
        Request $request
    ): Response {
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        $page = $request->get('page', null);

        $result = $this->handle(
            new SearchIssues(
                pageToken: $page,
                filter: new IssueFilter(
                    projects: [$project],
                    statusesIds: $project->backlogStatusesIds
                ),
            )
        );

        return $this->renderBlock(
            view: 'app/project/issue/project_view_issues_backlog.stream.html.twig',
            block: 'list_next',
            parameters: [
                'searchIssuesResult' => $result,
                'project' => $project,
            ]
        );
    }
}
