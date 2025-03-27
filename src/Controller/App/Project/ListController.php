<?php

namespace App\Controller\App\Project;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/projects',
    name: RouteCollection::LIST->value,
    methods: [Request::METHOD_GET],
)]
class ListController extends AbstractController
{

    public function __invoke(
        #[CurrentUser] User $user,
    ): Response
    {
        return $this->render(
            view: 'app/project/list.html.twig',
            parameters: [
                'projects' => $user->getProjects(),
            ]
        );
    }

}
