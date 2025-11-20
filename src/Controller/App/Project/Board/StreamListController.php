<?php

declare(strict_types=1);

namespace App\Controller\App\Project\Board;

use App\Controller\App\Project\AbstractController;
use App\Entity\Project;
use App\Repository\Jira\BoardRepository;
use JiraCloud\Board\Board;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route(
    path: '/project/{key}/boards',
)]
class StreamListController extends AbstractController
{
    public function __construct(
        private readonly BoardRepository $jiraBoardRepository,
    ) {
    }

    #[Route(
        path: '/list',
        name: RouteCollection::LIST->value,
        methods: [Request::METHOD_GET]
    )]
    public function list(
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        Request $request,
    ): Response {
        $this->setCurrentProject($project);
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        $boards = $this->jiraBoardRepository->getBoardByProject($project);
        $boards = array_filter($boards, function (Board $board) use ($project) {
            return $board->location->projectKey === $project->jiraKey;
        });

        return $this->render(
            view: 'app/project/board/list.stream.html.twig',
            parameters: [
                'boards' => $boards,
                'project' => $project,
            ]
        );
    }
}
