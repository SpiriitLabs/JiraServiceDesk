<?php

namespace App\Controller\App\Project;

use App\Entity\Project;
use App\Repository\Jira\ProjectRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route(
    path: '/project/{projectKey}/view',
    name: RouteCollection::STREAM_VIEW->value,
    methods: [Request::METHOD_GET]
)]
class StreamViewController extends AbstractController
{
    public function __construct(
        private readonly ProjectRepository $jiraProjectRepository,
    ) {
    }

    public function __invoke(
        #[MapEntity(mapping: [
            'projectKey' => 'jiraKey',
        ])]
        Project $project,
        Request $request,
    ): Response {
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return $this->render(
            view: 'app/project/view.stream.html.twig',
            parameters: [
                'project' => $project,
                'jiraProject' => $this->jiraProjectRepository->get($project->jiraId),
            ]
        );
    }
}
