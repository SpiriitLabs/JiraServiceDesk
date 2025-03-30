<?php

namespace App\Controller\App\Project;

use App\Entity\Project;
use App\Repository\Jira\BoardRepository;
use App\Repository\Jira\ProjectRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/project/{id}',
    name: RouteCollection::VIEW->value,
    methods: [Request::METHOD_GET],
)]
class ViewController extends AbstractController
{
    public function __construct(
        private readonly ProjectRepository $jiraProjectRepository,
        private readonly BoardRepository $jiraBoardRepository,
    ) {
    }

    public function __invoke(
        #[MapEntity(mapping: [
            'id' => 'id',
        ])]
        Project $project,
    ): Response {
        return $this->render(
            view: 'app/project/view.html.twig',
            parameters: [
                'entity' => $project,
                'jiraProject' => $this->jiraProjectRepository->get($project->jiraId),
                'boards' => $this->jiraBoardRepository->getBoardByProject($project),
            ],
        );
    }
}
