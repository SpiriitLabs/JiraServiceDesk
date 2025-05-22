<?php

namespace App\Message\Command\App\Issue\Handler;

use App\Controller\Common\CreateControllerTrait;
use App\Entity\User;
use App\Formatter\Jira\AdfHardBreakFormatter;
use App\Message\Command\App\Issue\AddAttachment;
use App\Message\Command\App\Issue\CreateIssue;
use App\Repository\Jira\IssueRepository;
use DH\Adf\Node\Block\Document;
use JiraCloud\ADF\AtlassianDocumentFormat;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use JiraCloud\Issue\IssueType;
use JiraCloud\Issue\Priority;
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

        $descriptionData = $this->appendCreator(
            $command->creator,
            AdfHardBreakFormatter::format((array) json_decode($command->description, true))
        );
        $adfDocument = Document::load($descriptionData);

        $issue = (new IssueField())
            ->setIssueType($jiraIssueType)
            ->setProjectKey($command->project->jiraKey)
            ->setProjectId($command->project->jiraId)
            ->setSummary($command->summary)
            ->setDescription(
                new AtlassianDocumentFormat($adfDocument)
            )
            ->addLabelAsString('from-client')
        ;

        $jiraPriority = new Priority();
        $jiraPriority->id = (string) $command->priority->jiraId;
        $issue->priority = $jiraPriority;

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

    /**
     * @param array<int,mixed> $data
     *
     * @return array<mixed>
     */
    private function appendCreator(User $creator, array $data): array
    {
        $data['content'][] = [
            'type' => 'paragraph',
            'content' => [
                [
                    'type' => 'text',
                    'text' => '--------------',
                ],
            ],
        ];
        $data['content'][] = [
            'type' => 'paragraph',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $creator->fullName,
                ],
            ],
        ];

        return $data;
    }
}
