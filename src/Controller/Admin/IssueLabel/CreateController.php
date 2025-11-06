<?php

declare(strict_types=1);

namespace App\Controller\Admin\IssueLabel;

use App\Controller\Common\CreateControllerTrait;
use App\Entity\IssueLabel;
use App\Exception\Project\IssueLabelAlreadyExistException;
use App\Exception\Project\IssueLabelNotValidException;
use App\Form\Admin\IssueLabel\IssueLabelFormType;
use App\Message\Command\Admin\IssueLabel\CreateIssueLabel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/issue-label/create',
    name: RouteCollection::CREATE->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class CreateController extends AbstractController
{
    use CreateControllerTrait;

    public function __invoke(
        Request $request,
    ): Response {
        $form = $this->createForm(type: IssueLabelFormType::class, data: new CreateIssueLabel());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var ?IssueLabel $issueLabelCreated */
                $issueLabelCreated = $this->handle($form->getData());
            } catch (HandlerFailedException $exception) {
                if (
                    $exception->getPrevious() instanceof IssueLabelAlreadyExistException
                    || $exception->getPrevious() instanceof IssueLabelNotValidException
                ) {
                    $this->addFlash(
                        type: 'danger',
                        message: $exception->getPrevious()
                            ->getMessage(),
                    );

                    return $this->redirectToRoute(RouteCollection::CREATE->prefixed());
                }
                throw $exception;
            }

            if ($issueLabelCreated !== null) {
                $this->addFlash(
                    type: 'info',
                    message: 'project.flashes.created',
                );

                return $this->redirectToRoute(
                    route: RouteCollection::LIST->prefixed(),
                );
            }

            $this->addFlash(
                type: 'danger',
                message: 'flash.error',
            );
        }

        return $this->render(
            view: 'admin/issue_label/create.html.twig',
            parameters: [
                'form' => $form->createView(),
            ],
        );
    }
}
