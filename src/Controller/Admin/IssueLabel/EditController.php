<?php

declare(strict_types=1);

namespace App\Controller\Admin\IssueLabel;

use App\Controller\Common\EditControllerTrait;
use App\Entity\IssueLabel;
use App\Form\Admin\IssueLabel\IssueLabelFormType;
use App\Message\Command\Admin\IssueLabel\EditIssueLabel;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/issue-label/{id}/edit',
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
        IssueLabel $issueLabel,
    ): Response {
        $form = $this->createForm(type: IssueLabelFormType::class, data: new EditIssueLabel(issueLabel: $issueLabel));
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
            view: 'admin/issue_label/edit.html.twig',
            parameters: [
                'entity' => $issueLabel,
                'form' => $form->createView(),
            ]
        );
    }
}
