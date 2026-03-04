<?php

declare(strict_types=1);

namespace App\Message\Command\App\Issue\Handler;

use App\Controller\Common\CreateControllerTrait;
use App\Formatter\Jira\AdfHardBreakFormatter;
use App\Message\Command\App\Issue\AddAttachment;
use App\Message\Command\App\Issue\CreateIssue;
use App\Message\Trait\AppendCreatorTrait;
use App\Repository\Jira\IssueRepository;
use App\Service\AdfImageProcessor;
use DH\Adf\Node\Block\Document;
use JiraCloud\ADF\AtlassianDocumentFormat;
use JiraCloud\Issue\Attachment;
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
    use AppendCreatorTrait;

    public function __construct(
        private readonly IssueRepository $issueRepository,
    ) {
    }

    public function __invoke(CreateIssue $command): ?Issue
    {
        $jiraIssueType = new IssueType();
        $jiraIssueType->id = (string) $command->type->jiraId;

        $descriptionData = AdfHardBreakFormatter::format(
            (array) json_decode($command->description, true)
        );

        // Normalize image nodes (TipTap format) to ADF mediaSingle > media
        $descriptionData = AdfImageProcessor::normalizeImageNodes($descriptionData);

        // Extract base64 images BEFORE Document::load() (which doesn't support data: URLs)
        $extracted = AdfImageProcessor::extractBase64Images($descriptionData);
        $descriptionData = $extracted['adf'];

        $descriptionData = $this->appendCreator($command->creator, $descriptionData);
        $descriptionData = AdfImageProcessor::sanitizeMediaAttrs($descriptionData);
        $adfDocument = Document::load($descriptionData);

        $issue = (new IssueField())
            ->setIssueType($jiraIssueType)
            ->setProjectKey($command->project->jiraKey)
            ->setProjectId((string) $command->project->jiraId)
            ->setSummary($command->summary)
            ->setDescription(
                new AtlassianDocumentFormat($adfDocument)
            )
        ;
        foreach ($command->creator->getJiraLabels() as $label) {
            $issue->addLabelAsString($label);
        }

        $jiraPriority = new Priority();
        $jiraPriority->id = (string) $command->priority->jiraId;
        $issue->priority = $jiraPriority;

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

        // Upload base64 images as attachments and update description with real references
        if ($extracted['files'] !== []) {
            $attachmentRefs = [];
            foreach ($extracted['files'] as $fileInfo) {
                $tempFile = AdfImageProcessor::createTempFileFromBase64(
                    $fileInfo['base64'],
                    $fileInfo['mimeType'],
                    $fileInfo['index'],
                );

                /** @var array<Attachment> $uploadedAttachments */
                $uploadedAttachments = $this->handle(
                    new AddAttachment($jiraIssue, $tempFile)
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
                    $extracted['adf'],
                    $attachmentRefs,
                );
                $updatedAdf = $this->appendCreator($command->creator, $updatedAdf);
                $updatedAdf = AdfImageProcessor::sanitizeMediaAttrs($updatedAdf);
                $updatedDocument = Document::load($updatedAdf);
                $updatedIssueField = (new IssueField())
                    ->setDescription(new AtlassianDocumentFormat($updatedDocument))
                ;
                $this->issueRepository->update($jiraIssue, $updatedIssueField);
            }
        }

        return $jiraIssue;
    }
}
