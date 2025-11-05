<?php

declare(strict_types=1);

namespace App\Controller\Admin\IssueLabel;

use App\Controller\Common\DeleteControllerTrait;
use App\Entity\IssueLabel;
use App\Message\Command\Admin\IssueLabel\DeleteIssueLabel;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/issue-label/{id}/delete',
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
        IssueLabel $issueLabel,
    ): RedirectResponse {
        $this->handle(
            new DeleteIssueLabel(
                $issueLabel,
            ),
        );

        $this->addFlash(
            type: 'success',
            message: 'flash.deleted',
        );

        return $this->redirectToRoute(RouteCollection::LIST->prefixed());
    }
}
