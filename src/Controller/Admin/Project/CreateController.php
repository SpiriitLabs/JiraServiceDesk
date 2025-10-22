<?php

declare(strict_types=1);

namespace App\Controller\Admin\Project;

use App\Controller\Common\CreateControllerTrait;
use App\Entity\Project;
use App\Exception\Project\ProjectAlreadyExistException;
use App\Form\Admin\Project\ProjectFormType;
use App\Message\Command\Admin\Project\CreateProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
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
                /** @var ?Project $projectCreated */
                $projectCreated = $this->handle($form->getData());
            } catch (HandlerFailedException $exception) {
                if ($exception->getPrevious() instanceof ProjectAlreadyExistException) {
                    $this->addFlash(
                        type: 'danger',
                        message: $exception->getPrevious()
                            ->getMessage(),
                    );

                    return $this->redirectToRoute(RouteCollection::CREATE->prefixed());
                }

                throw $exception;
            }

            if ($projectCreated !== null) {
                $this->addFlash(
                    type: 'info',
                    message: 'project.flashes.created',
                );

                return $this->redirectToRoute(
                    route: RouteCollection::EDIT->prefixed(),
                    parameters: [
                        'id' => $projectCreated->getId(),
                    ]
                );
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
