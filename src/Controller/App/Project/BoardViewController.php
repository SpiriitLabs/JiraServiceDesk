<?php

namespace App\Controller\App\Project;

use App\Entity\Project;
use App\Formatter\Jira\IssueKanbanFormatter;
use App\Repository\Jira\BoardRepository;
use JiraCloud\Issue\JqlQuery;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/project/{idProject}/board/{idBoard}/view',
    name: RouteCollection::PROJECT_BOARD_VIEW->value,
    methods: [Request::METHOD_GET],
)]
class BoardViewController extends AbstractController
{

    public function __construct(
        private readonly BoardRepository $boardRepository,
        private readonly IssueKanbanFormatter $kanbanFormatter,
    ) {
    }

    public function __invoke(
        #[MapEntity(mapping: ['idProject' => 'id'])] Project $project,
        string $idBoard,
    ): Response {
        $kanbanIssuesFormatted = $this->kanbanFormatter->format(
            $this->boardRepository->getBoardIssuesById(
                id: $idBoard,
                parameters: [
                    'maxResults' => 500,
                    'jql' => new JqlQuery()->addInExpression(JqlQuery::FIELD_LABELS, [ 'from-client' ])->getQuery(),
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
                ],
            )
        );

        return $this->render(
            view: 'app/project/board_view.html.twig',
            parameters: [
                'entity' => $project,
                'boardId' => $idBoard,
                'kanbanIssues' => $kanbanIssuesFormatted,
            ],
        );
    }

}
