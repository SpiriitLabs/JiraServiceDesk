<?php

namespace App\Controller\App\Project\Issue;

use App\Controller\App\Project\RouteCollection as AppProjectRouteCollection;
use App\Controller\Common\CreateControllerTrait;
use App\Entity\Project;
use App\Entity\User;
use App\Form\App\Issue\IssueFormType;
use App\Message\Command\App\Issue\CreateIssue;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/project/{projectKey}/issue/create',
    name: RouteCollection::CREATE->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class CreateController extends AbstractController
{
    use CreateControllerTrait;

    public function __invoke(
        Request $request,
        #[MapEntity(mapping: [
            'projectKey' => 'jiraKey',
        ])]
        Project $project,
        #[CurrentUser]
        User $user,
    ): Response {
        $form = $this->createForm(
            type: IssueFormType::class,
            data: new CreateIssue(
                project: $project,
                creator: $user,
            ),
            options: [
                'projectId' => $project->getId(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $issue = $this->handle($form->getData());

            if ($issue !== null) {
                $this->addFlash(
                    type: 'success',
                    message: 'flash.created',
                );

                return $this->redirectToRoute(
                    route: AppProjectRouteCollection::VIEW->prefixed(),
                    parameters: [
                        'key' => $project->jiraKey,
                    ]
                );
            }

            $this->addFlash(
                type: 'danger',
                message: 'flash.error',
            );
        }

        return $this->render(
            view: 'app/project/issue/create.html.twig',
            parameters: [
                'project' => $project,
                'form' => $form->createView(),
            ],
        );
    }
}
