<?php

namespace App\Controller\App\Project;

use App\Controller\Common\GetControllerTrait;
use App\Entity\Project;
use App\Message\Query\App\Project\GetKanbanIssueByBoardId;
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
    use GetControllerTrait;

    public function __invoke(
        #[MapEntity(mapping: [
            'idProject' => 'id',
        ])]
        Project $project,
        string $idBoard,
    ): Response {
        $kanbanIssuesFormatted = $this->handle(
            new GetKanbanIssueByBoardId($project, $idBoard),
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
