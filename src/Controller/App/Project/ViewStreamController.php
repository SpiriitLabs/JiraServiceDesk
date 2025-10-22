<?php

declare(strict_types=1);

namespace App\Controller\App\Project;

use App\Entity\Project;
use App\Repository\Jira\ProjectRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route(
    path: '/project/{key}/view-stream',
    name: RouteCollection::VIEW_STREAM->value,
    methods: [Request::METHOD_GET]
)]
class ViewStreamController extends AbstractController
{
    public function __construct(
        private readonly ProjectRepository $jiraProjectRepository,
    ) {
    }

    public function __invoke(
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        Request $request,
    ): Response {
        $this->setCurrentProject($project);
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return $this->render(
            view: 'app/project/view.stream.html.twig',
            parameters: [
                'project' => $project,
                'jiraProject' => $this->jiraProjectRepository->get((string) $project->jiraId),
            ]
        );
    }
}
