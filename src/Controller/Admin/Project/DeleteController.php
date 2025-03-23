<?php

namespace App\Controller\Admin\Project;

use App\Controller\Common\DeleteControllerTrait;
use App\Entity\Project;
use App\Message\Command\Common\DeleteEntity;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/project/{id}/delete',
    name: RouteCollection::DELETE->value,
    methods: [Request::METHOD_POST],
)]
class DeleteController extends AbstractController
{
    use DeleteControllerTrait;

    public function __invoke(
        #[MapEntity(mapping: [
            'id' => 'id',
        ])]
        Project $project,
    ): RedirectResponse {
        $this->handle(
            new DeleteEntity(
                class: Project::class,
                id: $project->getId(),
            ),
        );

        $this->addFlash(
            type: 'success',
            message: 'flash.deleted',
        );

        return $this->redirectToRoute(RouteCollection::LIST->prefixed());
    }
}
