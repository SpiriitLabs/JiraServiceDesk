<?php

declare(strict_types=1);

namespace App\Controller\App\Project;

use App\Entity\Project;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/project/{key}',
    name: RouteCollection::VIEW->value,
    methods: [Request::METHOD_GET],
)]
class ViewController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
    ): Response {
        $this->setCurrentProject($project);

        return $this->render(
            view: 'app/project/view.html.twig',
            parameters: [
                'entity' => $project,
            ],
        );
    }
}
