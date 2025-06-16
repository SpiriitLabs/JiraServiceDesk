<?php

namespace App\Controller\App\Project\Issue;

use App\Controller\App\Project\AbstractController;
use App\Controller\Common\GetControllerTrait;
use App\Entity\Project;
use App\Entity\User;
use App\Form\Filter\Issue\IssueFormFilter;
use App\Message\Query\App\Issue\SearchIssues;
use App\Model\Filter\IssueFilter;
use App\Model\SearchIssuesResult;
use App\Repository\ProjectRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/project/{key}/issues/list/backlog',
    name: RouteCollection::BACKLOG_LIST->value,
    methods: [Request::METHOD_GET],
)]
class BacklogListController extends AbstractController
{
    use GetControllerTrait;

    public function __invoke(
        Request $request,
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        #[CurrentUser]
        User $user,
        #[MapQueryParameter]
        IssueFilter $filter = new IssueFilter(),
    ): Response {
        $this->setCurrentProject($project);
        $filter->projects = [$this->getCurrentProject()];
        $filter->statusesIds = $project->backlogStatusesIds;
        $page = $request->get('page', null);
        $form = $this->createForm(IssueFormFilter::class, $filter, [
            'current_user' => $user,
        ]);
        $form->handleRequest($request);

        /** @var SearchIssuesResult $searchIssueResult */
        $searchIssueResult = $this->handle(
            new SearchIssues(
                sort: $request->get('_sort', '-updatedAt'),
                user: $user,
                filter: $filter,
                pageToken: $page,
            )
        );

        return $this->render(
            view: 'app/project/issue/list.html.twig',
            parameters: [
                'filterForm' => $form->createView(),
                'searchIssuesResult' => $searchIssueResult,
                'nextPage' => $searchIssueResult->nextPageToken,
            ]
        );
    }
}
