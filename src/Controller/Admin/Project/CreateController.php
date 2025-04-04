<?php

namespace App\Controller\Admin\Project;

use App\Controller\Common\CreateControllerTrait;
use App\Exception\Project\ProjectAlreadyExistException;
use App\Form\Admin\Project\ProjectFormType;
use App\Message\Command\Admin\Project\CreateProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/project/create',
    name: RouteCollection::CREATE->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class CreateController extends AbstractController
{
    use CreateControllerTrait;

    public function __invoke(
        Request $request,
    ): Response {
        $form = $this->createForm(type: ProjectFormType::class, data: new CreateProject());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $projectCreated = $this->handle($form->getData());
            } catch (\Exception $exception) {
                if ($exception->getPrevious() instanceof ProjectAlreadyExistException) {
                    $this->addFlash(
                        type: 'danger',
                        message: 'project.flashes.alreadyExist'
                    );

                    return $this->redirectToRoute(RouteCollection::CREATE->prefixed());
                }
            }

            if ($projectCreated !== null) {
                $this->addFlash(
                    type: 'success',
                    message: 'flash.created',
                );

                return $this->redirectToRoute(RouteCollection::LIST->prefixed());
            }

            $this->addFlash(
                type: 'danger',
                message: 'flash.error',
            );
        }

        return $this->render(
            view: 'admin/project/create.html.twig',
            parameters: [
                'form' => $form->createView(),
            ],
        );
    }
}
