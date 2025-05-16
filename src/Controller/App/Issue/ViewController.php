<?php

namespace App\Controller\App\Issue;

use App\Controller\Common\GetControllerTrait;
use App\Entity\User;
use App\Form\App\Issue\IssueCommentFormType;
use App\Message\Command\App\Issue\CreateComment;
use App\Message\Query\App\Issue\GetFullIssue;
use App\Message\Query\App\Project\GetProjectByJiraKey;
use App\Repository\Jira\IssueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\UX\Turbo\TurboBundle;

class ViewController extends AbstractController
{
    use GetControllerTrait;

    public function __construct(
        private IssueRepository $jiraIssueRepository,
    ) {
    }

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
    public function view(
        string $keyIssue,
        Request $request,
        #[CurrentUser]
        User $user,
        ?string $keyProject = null,
    ): Response {
        $issue = $this->jiraIssueRepository->getFull($keyIssue);
        $comments = $this->jiraIssueRepository->getCommentForIssue($keyIssue);
        $project = $keyProject !== null ? $this->handle(new GetProjectByJiraKey(jiraKey: $keyProject)) : null;

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
                    'keyIssue' => $keyIssue,
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
            ]
        );
    }

    #[Route(
        path: '/issue/{keyIssue}/attachments',
        name: RouteCollection::VIEW_ATTACHMENTS_STREAM->value,
        methods: [Request::METHOD_GET],
    )]
    public function viewAttachments(
        Request $request,
        string $keyIssue,
    ): Response {
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        $issue = $this->handle(new GetFullIssue($keyIssue));

        if (count($issue->customAttachments) == 0) {
            return new Response(status: 200);
        }

        return $this->render(
            view: 'app/issue/issue_attachment.stream.html.twig',
            parameters: [
                'issue' => $issue,
            ]
        );
    }
}
