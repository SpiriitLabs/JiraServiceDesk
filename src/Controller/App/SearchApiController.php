<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Controller\Common\GetControllerTrait;
use App\Entity\User;
use App\Message\Query\App\Issue\SearchIssues;
use App\Model\Filter\IssueFilter;
use App\Model\Filter\ProjectFilter;
use App\Model\SearchIssuesResult;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/search',
    name: RouteCollection::SEARCH_API->value,
    methods: [Request::METHOD_GET],
)]
class SearchApiController extends AbstractController
{
    use GetControllerTrait;

    public function __construct(
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(
        Request $request,
        #[CurrentUser]
        User $user,
    ): Response {
        $query = $request->query->get('query');

        $issuesFilter = new IssueFilter();
        $issuesFilter->query = $query;
        /** @var SearchIssuesResult $searchIssueResult */
        $searchIssueResult = $this->handle(
            new SearchIssues(
                sort: $request->query->get('_sort', '-updated'),
                user: $user,
                filter: $issuesFilter,
                maxIssuesResults: 5,
            )
        );
        if (count($searchIssueResult->issues) == 0) {
            /** @var SearchIssuesResult $searchIssueResult */
            $searchIssueResult = $this->handle(
                new SearchIssues(
                    sort: $request->query->get('_sort', '-updated'),
                    user: $user,
                    maxIssuesResults: 5,
                )
            );
        }

        $projects = $this->projectRepository->filter(
            filter: new ProjectFilter(
                user: $user,
                query: $query,
            ),
        )->getQuery()
            ->getResult()
        ;

        return $this->render(
            view: 'app/search.html.twig',
            parameters: [
                'issues' => $searchIssueResult->issues,
                'projects' => $projects,
            ],
        );
    }
}
