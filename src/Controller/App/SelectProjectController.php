<?php

namespace App\Controller\App;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/projects/select',
    name: RouteCollection::PROJECT_SELECT->value,
    methods: [Request::METHOD_GET],
)]
class SelectProjectController extends AbstractController
{
    public function __invoke(
        #[CurrentUser]
        User $user,
    ): Response {
        if ($user->defaultProject !== null) {
            return $this->redirectToRoute(
                'app_project_view',
                [
                    'key' => $user->defaultProject->jiraKey,
                ]
            );
        }

        return $this->render(
            view: 'app/select_project.html.twig',
        );
    }
}
