<?php

namespace App\Controller\App\Issue;

use App\Controller\Common\GetControllerTrait;
use App\Entity\User;
use App\Form\App\Issue\IssueCommentFormType;
use App\Message\Command\App\Issue\CreateComment;
use App\Message\Query\App\Issue\GetCommentsIssue;
use App\Message\Query\App\Issue\GetFullIssue;
use App\Message\Query\App\Project\GetProjectByJiraKey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/issue/{keyIssue}',
    name: RouteCollection::VIEW->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
#[Route(
    path: '/project/{keyProject}/issue/{keyIssue}',
    name: RouteCollection::PROJECT_VIEW->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class ViewController extends AbstractController
{
    use GetControllerTrait;

    public function __invoke(
        string $keyIssue,
        Request $request,
        #[CurrentUser]
        User $user,
        ?string $keyProject = null,
    ): Response {
        $issue = $this->handle(
            new GetFullIssue($keyIssue),
        );
        $comments = $this->handle(
            new GetCommentsIssue($keyIssue),
        );
        $project = $keyProject !== null ? $this->handle(new GetProjectByJiraKey(jiraKey: $keyProject)) : null;
        $refererUrl = $request->headers->get('referer');

        $form = $this->createForm(IssueCommentFormType::class, new CreateComment($issue, '', [], $user));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->handle($form->getData());

            $this->addFlash(
                type: 'success',
                message: 'flash.created',
            );

            return $this->redirectToRoute(
                route: RouteCollection::VIEW->prefixed(),
                parameters: [
                    'key' => $keyIssue,
                ],
            );
        }

        return $this->render(
            view: 'app/issue/view.html.twig',
            parameters: [
                'key' => $keyIssue,
                'issue' => $issue,
                'project' => $project,
                'comments' => $comments->comments,
                'commentForm' => $form->createView(),
                'refererUrl' => $refererUrl,
            ]
        );
    }
}
