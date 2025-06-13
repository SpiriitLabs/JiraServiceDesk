<?php

namespace App\Controller\App\Project\Board;

use App\Controller\App\Project\AbstractController;
use App\Controller\Common\GetControllerTrait;
use App\Entity\Project;
use App\Message\Query\App\Project\GetKanbanIssueByBoardId;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[Route(
    path: '/project/{key}/board/{idBoard}/view',
)]
#[IsGranted('ROLE_APP_VIEW_KANBAN')]
class ViewController extends AbstractController
{
    use GetControllerTrait;

    #[Route(
        path: '/',
        name: RouteCollection::VIEW->value,
        methods: [Request::METHOD_GET],
    )]
    public function view(
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        string $idBoard,
    ): Response {
        $this->setCurrentProject($project);

        return $this->render(
            view: 'app/project/board_view.html.twig',
            parameters: [
                'entity' => $project,
                'boardId' => $idBoard,
            ],
        );
    }

    #[Route(
        path: '/stream',
        name: RouteCollection::VIEW_STREAM->value,
        methods: [Request::METHOD_GET],
    )]
    public function viewStream(
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        string $idBoard,
        Request $request,
    ): Response {
        $this->setCurrentProject($project);
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        $kanbanIssuesFormatted = $this->handle(
            new GetKanbanIssueByBoardId($project, $idBoard),
        );

        return $this->render(
            view: 'app/project/board_view.stream.html.twig',
            parameters: [
                'entity' => $project,
                'boardId' => $idBoard,
                'kanbanIssues' => $kanbanIssuesFormatted,
            ],
        );
    }
}
