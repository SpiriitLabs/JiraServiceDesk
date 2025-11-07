<?php

declare(strict_types=1);

namespace App\Controller\App\Project\Issue;

use App\Controller\App\Project\AbstractController;
use App\Controller\Common\EditControllerTrait;
use App\Entity\Project;
use App\Entity\User;
use App\Form\App\Issue\EditIssueFormType;
use App\Message\Command\App\Issue\EditIssue;
use App\Message\Query\App\Issue\GetFullIssue;
use App\Message\Query\App\Issue\GetIssueAssignableUsers;
use App\Repository\IssueTypeRepository;
use App\Repository\PriorityRepository;
use App\Repository\ProjectRepository;
use JiraCloud\Issue\Issue;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/project/{key}/issue/{keyIssue}/edit',
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
        string $keyIssue,
        Request $request,
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        #[CurrentUser]
        User $user,
    ): Response {
        $this->setCurrentProject($project);
        /** @var Issue $issue */
        $issue = $this->handle(new GetFullIssue($keyIssue));
        $assignableUsers = $this->handle(new GetIssueAssignableUsers(user: $user, project: $project));

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
        $assigneesOptions = $assignableUsers;
        if ($issue->fields->assignee !== null) {
            $assigneesOptions[$issue->fields->assignee->displayName] = $issue->fields->assignee->accountId;
        }

        $form = $this->createForm(
            type: EditIssueFormType::class,
            data: new EditIssue(
                project: $project,
                issue: $issue,
                creator: $user,
                issueType: $this->issueTypeRepository->findOneBy([
                    'jiraId' => $issue->fields->issuetype->id,
                    'project' => $project,
                ]),
                priority: $this->priorityRepository->findOneBy([
                    'jiraId' => $issue->fields->priority->id,
                ]),
                transition: (string) $issueTransitionIdCurrentStatusIssue,
                assignee: $issue->fields->assignee->accountId ?? 'null',
            ),
            options: [
                'projectId' => $project->getId(),
                'transitions' => $issueTransitions,
                'assignee_editable' => $assignableUsers === $assigneesOptions,
                'assignees' => $assigneesOptions,
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
                        'key' => $this->getCurrentProject()
                            ->jiraKey,
                        'keyIssue' => $keyIssue,
                    ],
                );
            }

            $this->addFlash(
                type: 'danger',
                message: 'flash.error',
            );
        }

        return $this->render(
            view: 'app/project/issue/edit.html.twig',
            parameters: [
                'key' => $keyIssue,
                'issue' => $issue,
                'form' => $form->createView(),
            ],
        );
    }
}
