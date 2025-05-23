<?php

namespace App\Controller\App\Issue;

use App\Controller\Common\EditControllerTrait;
use App\Entity\User;
use App\Form\App\Issue\EditIssueFormType;
use App\Message\Command\App\Issue\EditIssue;
use App\Message\Query\App\Issue\GetFullIssue;
use App\Message\Query\App\Issue\GetIssueAssignableUsers;
use App\Repository\IssueTypeRepository;
use App\Repository\PriorityRepository;
use App\Repository\ProjectRepository;
use App\Security\Voter\ProjectVoter;
use JiraCloud\Issue\Issue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/issue/{key}/edit',
    name: RouteCollection::EDIT->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class EditController extends AbstractController
{
    use EditControllerTrait;

    public function __construct(
        private readonly IssueTypeRepository $issueTypeRepository,
        private readonly PriorityRepository $priorityRepository,
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(
        string $key,
        Request $request,
        #[CurrentUser]
        User $user,
    ): Response {
        /** @var Issue $issue */
        $issue = $this->handle(
            new GetFullIssue($key),
        );
        $issueTransitions = [];
        foreach ($issue->transitions as $issueTransition) {
            $issueTransitions[$issueTransition->name] = $issueTransition->id;
        }
        $issueTransitionIdCurrentStatusIssue = null;
        foreach ($issue->transitions as $issueTransition) {
            if ($issue->fields->status->id == $issueTransition->to->id) {
                $issueTransitionIdCurrentStatusIssue = $issueTransition->id;
                break;
            }
        }
        $project = $this->projectRepository->findOneBy([
            'jiraKey' => $issue->fields->project->key,
        ]);
        if ($project == null) {
            $this->addFlash(
                type: 'danger',
                message: 'project.flashes.notFound',
            );

            return $this->redirect(
                $request->headers->get('referer'),
            );
        }
        $assignableUsers = $this->handle(new GetIssueAssignableUsers($project));
        $this->denyAccessUnlessGranted(ProjectVoter::PROJECT_ACCESS, $project);

        $form = $this->createForm(
            type: EditIssueFormType::class,
            data: new EditIssue(
                project: $project,
                issue: $issue,
                issueType: $this->issueTypeRepository->findOneBy([
                    'jiraId' => $issue->fields->issuetype->id,
                    'project' => $project,
                ]),
                priority: $this->priorityRepository->findOneBy([
                    'jiraId' => $issue->fields->priority->id,
                ]),
                transition: $issueTransitionIdCurrentStatusIssue,
                assignee: $issue->fields->assignee->accountId ?? 'null',
            ),
            options: [
                'projectId' => $project?->getId() ?? null,
                'transitions' => $issueTransitions,
                'assignees' => $assignableUsers,
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $jiraIssueEdited = $this->handle($form->getData());

            if ($jiraIssueEdited !== null) {
                $this->addFlash(
                    type: 'success',
                    message: 'flash.edited',
                );

                return $this->redirectToRoute(
                    route: RouteCollection::VIEW->prefixed(),
                    parameters: [
                        'keyIssue' => $key,
                    ],
                );
            }

            $this->addFlash(
                type: 'danger',
                message: 'flash.error',
            );
        }

        return $this->render(
            view: 'app/issue/edit.html.twig',
            parameters: [
                'key' => $key,
                'issue' => $issue,
                'form' => $form->createView(),
            ],
        );
    }
}
