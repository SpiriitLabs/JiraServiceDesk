<?php

declare(strict_types=1);

namespace App\Message\Command\App\Issue\Handler;

use App\Controller\Common\EditControllerTrait;
use App\Message\Command\App\Issue\EditIssue;
use App\Message\Command\App\Issue\TransitionTo;
use App\Repository\Jira\IssueRepository;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use JiraCloud\Issue\IssueType;
use JiraCloud\Issue\Priority;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EditIssueHandler
{
    use EditControllerTrait;

    public function __construct(
        private readonly IssueRepository $issueRepository,
    ) {
    }

    public function __invoke(EditIssue $command): ?Issue
    {
        $jiraIssueType = new IssueType();
        $jiraIssueType->id = (string) $command->type->jiraId;

        $issueField = (new IssueField())
            ->setIssueType($jiraIssueType)
            ->setProjectKey($command->project->jiraKey)
            ->setProjectId((string) $command->project->jiraId)
            ->setSummary($command->summary)
            ->addLabelAsString('from-client')
        ;

        $jiraPriority = new Priority();
        $jiraPriority->id = (string) $command->priority->jiraId;
        $issueField->priority = $jiraPriority;

        if ($command->assignee !== 'null') {
            $issueField->setAssigneeAccountId($command->assignee);
        } else {
            $issueField->setAssigneeToUnassigned();
        }

        $jiraIssueKey = $this->issueRepository->update($command->issue, $issueField);

        if ($jiraIssueKey == null) {
            return null;
        }
        $transitionToApply = null;
        foreach ($command->issue->transitions as $issueTransition) {
            if ($command->transition == $issueTransition->id) {
                $transitionToApply = $issueTransition;
            }
        }
        // Handle transition change only if needed.
        if ($transitionToApply->to->id !== $command->issue->fields->status->id) {
            $this->handle(
                new TransitionTo(
                    $command->issue->id,
                    $command->transition,
                ),
            );
        }

        return $command->issue;
    }
}
