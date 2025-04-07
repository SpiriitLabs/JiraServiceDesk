<?php

namespace App\Controller\App\Issue;

use App\Controller\Common\EditControllerTrait;
use App\Controller\Common\GetRefererRequestTrait;
use App\Message\Command\App\Issue\TransitionTo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/issue/{issueId}/transition/{transitionId}',
    name: RouteCollection::TRANSITION_TO->value,
    methods: [Request::METHOD_POST],
)]
class TransitionToController extends AbstractController
{
    use EditControllerTrait;
    use GetRefererRequestTrait;

    public function __invoke(
        Request $request,
        string $issueId,
        string $transitionId,
    ): Response {
        return new Response('', Response::HTTP_BAD_REQUEST);
        $this->handle(
            new TransitionTo(
                issueId: $issueId,
                transitionId: $transitionId,
            ),
        );

        return $this->redirect(
            $this->getRefererLink($request),
        );
    }
}
