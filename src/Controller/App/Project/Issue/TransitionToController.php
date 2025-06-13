<?php

namespace App\Controller\App\Project\Issue;

use App\Controller\App\Project\AbstractController;
use App\Controller\Common\EditControllerTrait;
use App\Controller\Common\GetRefererRequestTrait;
use App\Entity\Project;
use App\Message\Command\App\Issue\TransitionTo;
use JiraCloud\JiraException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/project/{key}/issue/{issueId}/transition/{transitionId}',
)]
class TransitionToController extends AbstractController
{
    use EditControllerTrait;
    use GetRefererRequestTrait;

    #[Route(
        path: '/',
        name: RouteCollection::TRANSITION_TO->value,
        methods: [Request::METHOD_POST],
    )]
    public function updateTransitionAndReturnToReferer(
        Request $request,
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        string $issueId,
        string $transitionId,
    ): RedirectResponse {
        $this->setCurrentProject($project);
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
        path: '/api',
        name: RouteCollection::API_TRANSITION_TO->value,
        methods: [Request::METHOD_POST],
    )]
    public function apiUpdateTransition(
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        string $issueId,
        string $transitionId,
    ): Response {
        $this->setCurrentProject($project);
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
