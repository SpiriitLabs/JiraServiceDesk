<?php

namespace App\Controller\App;

use App\Controller\Common\GetControllerTrait;
use App\Entity\User;
use App\Message\Query\App\Issue\SearchIssues;
use App\Model\SearchIssuesResult;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/dashboard',
    name: RouteCollection::DASHBOARD->value,
    methods: [Request::METHOD_GET]
)]
class DashboardController extends AbstractController
{
    use GetControllerTrait;

    public function __invoke(
        #[CurrentUser]
        User $user,
        Request $request,
    ): Response {
        $page = $request->get('page', 1);

        /** @var SearchIssuesResult $searchIssueResult */
        $searchIssueResult = $this->handle(
            new SearchIssues(
                sort: $request->get('_sort', 'id'),
                page: $page,
                user: $user,
                onlyUserAssigned: true,
            )
        );

        if ($page > ($searchIssueResult->page + 1)) {
            return $this->redirectToRoute(RouteCollection::DASHBOARD->prefixed());
        }


        return $this->render(
            view: 'app/dashboard.html.twig',
            parameters: [
                'projects' => $user->getProjects(),
                'searchIssuesResult' => $searchIssueResult,
                'currentPage' => $page,
                'previousPage' => ($page - 1) < 1 ? null : ($page - 1),
                'nextPage' => ($page + 1) > ($searchIssueResult->page + 1) ? null : $page + 1,
                'maxPage' => $searchIssueResult->page + 1,
            ]
        );
    }
}
