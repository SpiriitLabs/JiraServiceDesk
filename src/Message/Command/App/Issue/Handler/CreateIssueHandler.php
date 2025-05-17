<?php

namespace App\Message\Command\App\Issue\Handler;

use App\Controller\Common\CreateControllerTrait;
use App\Message\Command\App\Issue\AddAttachment;
use App\Message\Command\App\Issue\CreateIssue;
use App\Repository\Jira\IssueRepository;
use DH\Adf\Node\Block\Document;
use JiraCloud\ADF\AtlassianDocumentFormat;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use JiraCloud\Issue\IssueType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateIssueHandler
{
    use CreateControllerTrait;

    public function __construct(
        private readonly IssueRepository $issueRepository,
    ) {
    }

    public function __invoke(CreateIssue $command): ?Issue
    {
        $jiraIssueType = new IssueType();
        $jiraIssueType->id = $command->type->jiraId;
        // $description = (new Document())
        //     ->paragraph()
        //     ->text($command->description)
        //     ->break()
        //     ->break()
        //     ->text('-------')
        //     ->break()
        //     ->text($command->creator->fullName)
        //     ->end()
        // ;

        $issue = (new IssueField())
            ->setIssueType($jiraIssueType)
            ->setProjectKey($command->project->jiraKey)
            ->setProjectId($command->project->jiraId)
            ->setSummary($command->summary)
            ->setDescription(
                new AtlassianDocumentFormat(Document::load((array) json_decode($command->description, true)))
            )
            ->setPriorityNameAsString($command->priority->name)
            ->addLabelAsString('from-client')
        ;

        if ($command->assignee !== 'null') {
            $issue->setAssigneeAccountId($command->assignee);
        } else {
            $issue->setAssigneeToUnassigned();
        }

        $jiraIssue = $this->issueRepository->create($issue);

        foreach ($command->attachments as $attachment) {
            /** @var UploadedFile $attachment */
            $this->handle(
                new AddAttachment(
                    $jiraIssue,
                    $attachment,
                )
            );
        }

        return $jiraIssue;
    }
}
