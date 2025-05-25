<?php

namespace App\Controller\App\Project\Board;

use App\Entity\Project;
use App\Repository\Jira\BoardRepository;
use App\Security\Voter\ProjectVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route(
    path: '/project/{projectKey}/boards',
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
            'projectKey' => 'jiraKey',
        ])]
        Project $project,
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted(ProjectVoter::PROJECT_ACCESS, $project);
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        $boards = $this->jiraBoardRepository->getBoardByProject($project);

        return $this->render(
            view: 'app/project/board/list.stream.html.twig',
            parameters: [
                'boards' => $boards,
                'project' => $project,
            ]
        );
    }
}
