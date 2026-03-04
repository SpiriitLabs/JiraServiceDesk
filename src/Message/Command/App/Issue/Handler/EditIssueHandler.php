<?php

declare(strict_types=1);

namespace App\Message\Command\App\Issue\Handler;

use App\Controller\Common\EditControllerTrait;
use App\Formatter\Jira\AdfHardBreakFormatter;
use App\Message\Command\App\Issue\AddAttachment;
use App\Message\Command\App\Issue\EditIssue;
use App\Message\Command\App\Issue\TransitionTo;
use App\Repository\Jira\IssueRepository;
use App\Service\AdfImageProcessor;
use App\Service\RawAtlassianDocumentFormat;
use JiraCloud\Issue\Attachment;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use JiraCloud\Issue\IssueType;
use JiraCloud\Issue\Priority;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
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
        ;

        // Preserve existing labels and add creator's labels if not present
        $labels = $command->issue->fields->labels ?? [];
        foreach ($command->creator->getJiraLabels() as $creatorLabel) {
            if (! in_array($creatorLabel, $labels, true)) {
                $labels[] = $creatorLabel;
            }
        }
        foreach ($labels as $label) {
            $issueField->addLabelAsString($label);
        }

        $extractedFiles = [];
        $extractedAdf = [];
        if ($command->description !== null && $command->description !== '') {
            $descriptionData = AdfHardBreakFormatter::format(
                (array) json_decode($command->description, true)
            );
            // Normalize stray image nodes and extract base64 images
            $descriptionData = AdfImageProcessor::normalizeImageNodes($descriptionData);
            $extracted = AdfImageProcessor::extractBase64Images($descriptionData);
            $descriptionData = $extracted['adf'];
            $extractedFiles = $extracted['files'];
            $extractedAdf = $extracted['adf'];

            // Sanitize media attrs and clean TipTap artifacts
            // Uses RawAtlassianDocumentFormat to support type:"external" media nodes
            $descriptionData = AdfImageProcessor::sanitizeMediaAttrs($descriptionData);
            $descriptionData = AdfImageProcessor::cleanForJira($descriptionData);

            $issueField->setDescription(
                new RawAtlassianDocumentFormat($descriptionData)
            );
        }

        $jiraPriority = new Priority();
        $jiraPriority->id = (string) $command->priority->jiraId;
        $issueField->priority = $jiraPriority;

        if ($command->assignee !== 'null') {
            $issueField->setAssigneeAccountId($command->assignee);
        } else {
            $issueField->setAssigneeToUnassigned();
        }

        $jiraIssueKey = $this->issueRepository->update($command->issue, $issueField);

        // Upload base64 images as attachments and update description with real references
        if ($extractedFiles !== []) {
            $attachmentRefs = [];
            foreach ($extractedFiles as $fileInfo) {
                $tempFile = AdfImageProcessor::createTempFileFromBase64(
                    $fileInfo['base64'],
                    $fileInfo['mimeType'],
                    $fileInfo['index'],
                );

                /** @var array<Attachment> $uploadedAttachments */
                $uploadedAttachments = $this->handle(
                    new AddAttachment($command->issue, $tempFile)
                );

                if ($uploadedAttachments !== [] && isset($uploadedAttachments[0])) {
                    $attachmentRefs[$fileInfo['index']] = [
                        'id' => $uploadedAttachments[0]->id,
                        'filename' => $uploadedAttachments[0]->filename,
                    ];
                }
            }

            if ($attachmentRefs !== []) {
                $updatedAdf = AdfImageProcessor::replaceWithAttachments(
                    $extractedAdf,
                    $attachmentRefs,
                );
                $updatedAdf = AdfImageProcessor::sanitizeMediaAttrs($updatedAdf);
                $updatedAdf = AdfImageProcessor::cleanForJira($updatedAdf);
                $updatedIssueField = (new IssueField())
                    ->setDescription(new RawAtlassianDocumentFormat($updatedAdf))
                ;
                $this->issueRepository->update($command->issue, $updatedIssueField);
            }
        }

        $cache = new FilesystemAdapter();
        $cache->deleteItem(sprintf('jira.full_issue_%s', $command->issue->key));

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
