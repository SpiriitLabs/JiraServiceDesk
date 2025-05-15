<?php

namespace App\Controller\App\Issue;

use App\Controller\App\Project\Issue\RouteCollection as IssueRouteCollection;
use App\Controller\Common\CreateControllerTrait;
use App\Entity\User;
use App\Form\App\Project\SelectProjectFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/issues/create',
    name: RouteCollection::CREATE->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class CreateController extends AbstractController
{
    use CreateControllerTrait;

    public function __invoke(
        Request $request,
        #[CurrentUser]
        User $user,
    ): Response {
        $form = $this->createForm(
            type: SelectProjectFormType::class,
            options: [
                'current_user' => $user,
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectSelected = $form->get('project')
                ->getData()
            ;

            return $this->redirectToRoute(
                route: IssueRouteCollection::CREATE->prefixed(),
                parameters: [
                    'projectKey' => $projectSelected->jiraKey,
                ],
            );
        }

        return $this->render(
            view: 'app/issue/create_select_project.html.twig',
            parameters: [
                'form' => $form->createView(),
            ],
        );
    }
}
