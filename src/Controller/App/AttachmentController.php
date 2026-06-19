<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Controller\Common\GetControllerTrait;
use App\Entity\Project;
use App\Formatter\Jira\IssueAttachmentFormatter;
use App\Message\Query\App\Issue\GetFullIssue;
use App\Security\Voter\ProjectVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/attachment/{key}/{keyIssue}'
)]
class AttachmentController extends AbstractController
{
    use GetControllerTrait;

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

    #[Route(
        path: '/preview/{attachmentId}',
        name: RouteCollection::ATTACHMENT_PREVIEW->value,
    )]
    public function preview(
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        string $keyIssue,
        string $attachmentId,
    ): Response|BinaryFileResponse {
        $this->denyAccessUnlessAttachmentIsGranted($project, $keyIssue, $attachmentId);

        $thumbnailPath = sprintf('%s/preview-%s', $this->issueAttachmentDirectory, $attachmentId);
        if ($this->filesystem->exists($thumbnailPath) === false) {
            return new Response(status: 404);
        }

        return new BinaryFileResponse(
            $thumbnailPath,
        );
    }

    #[Route(
        path: '/{attachmentId}',
        name: RouteCollection::ATTACHMENT->value,
    )]
    public function download(
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        string $keyIssue,
        string $attachmentId,
    ): Response|BinaryFileResponse {
        $this->denyAccessUnlessAttachmentIsGranted($project, $keyIssue, $attachmentId);

        $filePath = sprintf('%s/%s', $this->issueAttachmentDirectory, $attachmentId);
        if ($this->filesystem->exists($filePath) === false) {
            return new Response(status: 404);
        }

        return new BinaryFileResponse(
            $filePath,
        );
    }

    /**
     * Ensure the current user belongs to the project that actually owns the
     * attachment before serving the file from disk.
     */
    private function denyAccessUnlessAttachmentIsGranted(
        Project $project,
        string $keyIssue,
        string $attachmentId,
    ): void {
        $this->denyAccessUnlessGranted(ProjectVoter::PROJECT_ACCESS, $project);

        try {
            $issue = $this->handle(new GetFullIssue($keyIssue));
        } catch (HandlerFailedException) {
            throw $this->createNotFoundException();
        }

        if ($issue->fields->project->key !== $project->jiraKey) {
            throw $this->createNotFoundException();
        }

        $attachmentIds = array_map(
            static fn (array $attachment): string => (string) $attachment['id'],
            $issue->customAttachments ?? []
        );

        if (! in_array($attachmentId, $attachmentIds, true)) {
            throw $this->createNotFoundException();
        }
    }
}
