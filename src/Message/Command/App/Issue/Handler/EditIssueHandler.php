<?php

namespace App\Message\Command\App\Issue\Handler;

use App\Controller\Common\EditControllerTrait;
use App\Message\Command\App\Issue\EditIssue;
use App\Message\Command\App\Issue\TransitionTo;
use App\Repository\Jira\IssueRepository;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use JiraCloud\Issue\IssueType;
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
        $jiraIssueType->id = $command->type->jiraId;

        $issueField = (new IssueField())
            ->setIssueType($jiraIssueType)
            ->setProjectKey($command->project->jiraKey)
            ->setProjectId($command->project->jiraId)
            ->setSummary($command->summary)
            ->setPriorityNameAsString($command->priority->name)
            ->addLabelAsString('from-client')
        ;

        if ($command->assignee !== 'null') {
            $issueField->setAssigneeAccountId($command->assignee);
        } else {
            $issueField->setAssigneeToUnassigned();
        }

        $jiraIssueKey = $this->issueRepository->update($command->issue, $issueField);

        if ($jiraIssueKey == null) {
            return null;
        }

        $this->handle(
            new TransitionTo(
                $command->issue->id,
                $command->transition,
            ),
        );

        return $command->issue;
    }
}
