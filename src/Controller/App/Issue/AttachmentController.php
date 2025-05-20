<?php

namespace App\Controller\App\Issue;

use App\Formatter\Jira\IssueAttachmentFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/attachment'
)]
class AttachmentController extends AbstractController
{
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
        string $attachmentId,
    ): Response|BinaryFileResponse {
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
        string $attachmentId,
    ): Response|BinaryFileResponse {
        $filePath = sprintf('%s/%s', $this->issueAttachmentDirectory, $attachmentId);
        if ($this->filesystem->exists($filePath) === false) {
            return new Response(status: 404);
        }

        return new BinaryFileResponse(
            $filePath,
        );
    }
}
