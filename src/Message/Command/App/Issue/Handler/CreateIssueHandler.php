<?php

namespace App\Message\Command\App\Issue\Handler;

use App\Message\Command\App\Issue\CreateIssue;
use App\Repository\Jira\IssueRepository;
use DH\Adf\Node\Block\Document;
use JiraCloud\ADF\AtlassianDocumentFormat;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use JiraCloud\Issue\IssueType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateIssueHandler
{
    public function __construct(
        private IssueRepository $issueRepository,
    ) {
    }

    public function __invoke(CreateIssue $command): ?Issue
    {
        $jiraIssueType = new IssueType();
        $jiraIssueType->id = $command->type->jiraId;
        $description = new Document()
            ->paragraph()
            ->text($command->description)
            ->break()
            ->break()
            ->text('-------')
            ->break()
            ->text($command->creator->fullName)
            ->end()
        ;

        $issue = new IssueField()
            ->setIssueType($jiraIssueType)
            ->setProjectKey($command->project->jiraKey)
            ->setProjectId($command->project->jiraId)
            ->setSummary($command->summary)
            ->setDescription(new AtlassianDocumentFormat($description))
            ->setPriorityNameAsString($command->priority->value)
            ->addLabelAsString('from-client')
        ;

        return $this->issueRepository->create($issue);
    }
}
