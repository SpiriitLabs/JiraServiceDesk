<?php

namespace App\Controller\App\Issue;

use App\Controller\Common\EditControllerTrait;
use App\Controller\Common\GetRefererRequestTrait;
use App\Message\Command\App\Issue\TransitionTo;
use JiraCloud\JiraException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransitionToController extends AbstractController
{
    use EditControllerTrait;
    use GetRefererRequestTrait;

    #[Route(
        path: '/issue/{issueId}/transition/{transitionId}',
        name: RouteCollection::TRANSITION_TO->value,
        methods: [Request::METHOD_POST],
    )]
    public function updateTransitionAndReturnToReferer(
        Request $request,
        string $issueId,
        string $transitionId,
    ): RedirectResponse {
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

    #[Route(
        path: '/api/issue/{issueId}/transition/{transitionId}',
        name: RouteCollection::API_TRANSITION_TO->value,
        methods: [Request::METHOD_POST],
    )]
    public function apiUpdateTransition(
        string $issueId,
        string $transitionId,
    ): Response {
        try {
            $this->handle(
                new TransitionTo(
                    issueId: $issueId,
                    transitionId: $transitionId,
                ),
            );

            return new Response(status: Response::HTTP_OK);
        } catch (JiraException $exception) {
            return new Response(status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
