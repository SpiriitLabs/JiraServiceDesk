<?php

namespace App\Controller\App\Project\Issue;

use App\Controller\App\Project\AbstractController;
use App\Controller\Common\GetControllerTrait;
use App\Entity\Project;
use App\Entity\User;
use App\Message\Query\App\Issue\SearchIssues;
use App\Model\Filter\IssueFilter;
use App\Model\SearchIssuesResult;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\UX\Turbo\TurboBundle;

#[Route(
    path: '/project/{key}/issues/user',
)]
class ShowUserStreamController extends AbstractController
{
    use GetControllerTrait;

    #[Route(
        path: '/list',
        name: RouteCollection::SHOW_USER_LIST->value,
        methods: [Request::METHOD_GET],
    )]
    public function list(
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        #[CurrentUser]
        User $user,
        Request $request,
    ): Response {
        $this->setCurrentProject($project);
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        $page = $request->get('page', null);
        $defaultSort = '-updatedAt';
        $sort = $request->get('_sort', $defaultSort);

        $issueFilter = new IssueFilter(
            projects: [$project],
        );
        /** @var SearchIssuesResult $searchIssueResult */
        $searchIssueResult = $this->handle(
            new SearchIssues(
                sort: $sort,
                onlyUserAssigned: true,
                filter: $issueFilter,
                maxIssuesResults: ($sort !== $defaultSort ? 1000 : SearchIssues::MAX_ISSUES_RESULTS),
                pageToken: $page,
            )
        );

        return $this->renderBlock(
            view: 'app/project/issue/project_view_issues_user.stream.html.twig',
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
        name: RouteCollection::SHOW_USER_LIST_NEXT->value,
        methods: [Request::METHOD_GET],
    )]
    public function streamBacklog(
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        #[CurrentUser]
        User $user,
        Request $request
    ): Response {
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        $page = $request->get('page', null);

        $result = $this->handle(
            new SearchIssues(
                onlyUserAssigned: true,
                filter: new IssueFilter(
                    projects: [$project],
                    statusesIds: $project->backlogStatusesIds
                ),
                pageToken: $page,
            )
        );

        return $this->renderBlock(
            view: 'app/project/issue/project_view_issues_user.stream.html.twig',
            block: 'list_next',
            parameters: [
                'searchIssuesResult' => $result,
                'project' => $project,
            ]
        );
    }
}
