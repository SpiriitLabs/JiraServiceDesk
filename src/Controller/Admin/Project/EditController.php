<?php

namespace App\Controller\Admin\Project;

use App\Controller\Common\EditControllerTrait;
use App\Entity\Project;
use App\Form\Admin\Project\ProjectFormType;
use App\Message\Command\Admin\Project\EditProject;
use App\Message\Query\App\Project\GetProjectRolesByJiraKey;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/project/{id}/edit',
    name: RouteCollection::EDIT->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class EditController extends AbstractController
{
    use EditControllerTrait;

    public function __invoke(
        Request $request,
        #[MapEntity(mapping: [
            'id' => 'id',
        ])]
        Project $project,
    ): Response {
        $form = $this->createForm(type: ProjectFormType::class, data: new EditProject($project), options: [
            'editable' => true,
            'roles' => $this->handle(new GetProjectRolesByJiraKey($project->jiraKey)),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handle($form->getData());

            $this->addFlash(
                type: 'success',
                message: 'flash.edited',
            );

            return $this->redirectToRoute(RouteCollection::LIST->prefixed());
        }

        return $this->render(
            view: 'admin/project/edit.html.twig',
            parameters: [
                'entity' => $project,
                'form' => $form->createView(),
            ]
        );
    }
}
