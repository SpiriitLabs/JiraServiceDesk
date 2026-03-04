<?php

declare(strict_types=1);

namespace App\Controller\App\Project\Issue;

use App\Controller\App\Project\AbstractController;
use App\Controller\Common\EditControllerTrait;
use App\Entity\Project;
use App\Formatter\Jira\IssueAttachmentFormatter;
use App\Message\Command\App\Issue\AddAttachment;
use App\Message\Query\App\Issue\GetFullIssue;
use JiraCloud\Attachment\AttachmentService;
use JiraCloud\Issue\Attachment;
use JiraCloud\Issue\Issue;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/project/{key}/issue/{issueKey}/upload-image',
    name: RouteCollection::UPLOAD_IMAGE->value,
    methods: [Request::METHOD_POST],
)]
class ImageUploadController extends AbstractController
{
    use EditControllerTrait;

    private string $issueAttachmentDirectory;

    public function __construct(
        private readonly Filesystem $filesystem,
        #[Autowire(param: 'upload_dir')]
        string $uploadDir,
    ) {
        $this->issueAttachmentDirectory = sprintf(
            '%s/%s',
            $uploadDir,
            IssueAttachmentFormatter::ISSUE_ATTACHMENT_FOLDER
        );
    }

    public function __invoke(
        string $issueKey,
        Request $request,
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
    ): Response {
        $this->setCurrentProject($project);

        $file = $request->files->get('file');
        if ($file === null) {
            return new JsonResponse([
                'error' => 'No file provided',
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Issue $issue */
        $issue = $this->handle(new GetFullIssue($issueKey));

        /** @var array<Attachment> $attachments */
        $attachments = $this->handle(
            new AddAttachment(
                issue: $issue,
                file: $file,
            )
        );

        if ($attachments === [] || ! isset($attachments[0])) {
            return new JsonResponse([
                'error' => 'Upload failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $attachment = $attachments[0];

        // Cache the file locally so /attachment/{id} serves it immediately
        $localPath = sprintf('%s/%s', $this->issueAttachmentDirectory, $attachment->id);
        if (! $this->filesystem->exists($this->issueAttachmentDirectory)) {
            $this->filesystem->mkdir($this->issueAttachmentDirectory);
        }
        $this->filesystem->copy($file->getPathname(), $localPath);

        // Fetch mediaApiFileId (UUID) from Jira — needed for ADF type:"file" media nodes
        $mediaFileId = null;
        $attachmentService = new AttachmentService();
        $rawJson = $attachmentService->exec('/attachment/' . $attachment->id);
        $attachmentData = json_decode($rawJson, true);
        if (isset($attachmentData['mediaApiFileId'])) {
            $mediaFileId = $attachmentData['mediaApiFileId'];
        }

        return new JsonResponse([
            'url' => $this->generateUrl('app_attachment', [
                'attachmentId' => $attachment->id,
            ]),
            'id' => $attachment->id,
            'filename' => $attachment->filename,
            'mediaFileId' => $mediaFileId,
        ]);
    }
}
