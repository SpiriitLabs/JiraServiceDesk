<?php

namespace App\Formatter\Jira;

use JiraCloud\Attachment\AttachmentService;
use JiraCloud\Issue\Issue;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class IssueAttachmentFormatter
{
    public const ISSUE_ATTACHMENT_FOLDER = 'issue_attachments';

    private AttachmentService $service;

    private string $issueAttachmentDirectory;

    public function __construct(
        private readonly Filesystem $filesystem,
        #[Autowire(param: 'upload_dir')]
        string $uploadDir,
    ) {
        $this->service = new AttachmentService();
        $this->issueAttachmentDirectory = sprintf('%s/%s', $uploadDir, self::ISSUE_ATTACHMENT_FOLDER);
    }

    /**
     * Download and return a list of attachment in array.
     */
    public function format(Issue $issue): Issue
    {
        $issueAttachment = [];
        foreach ($issue->fields->attachment as $attachment) {
            $attachmentPath = sprintf('%s/%s', $this->issueAttachmentDirectory, $attachment->id);
            if ($this->filesystem->exists($attachmentPath) === false) {
                $this->service->get($attachment->id, $this->issueAttachmentDirectory);
            }

            $thumbnailPath = sprintf('%s/preview-%s', $this->issueAttachmentDirectory, $attachment->id);
            if ($this->filesystem->exists($thumbnailPath) === false) {
                $thumbnailUrl = str_replace(
                    $attachment->id,
                    sprintf('thumbnail/%d', $attachment->id),
                    $attachment->self
                );

                $this->service->download(
                    url: $thumbnailUrl,
                    outDir: $this->issueAttachmentDirectory,
                    file: sprintf('preview-%s', $attachment->id)
                );
            }

            $issueAttachment[] = [
                'id' => $attachment->id,
                'name' => $attachment->filename ?? null,
                'author' => $attachment->author,
                'url' => sprintf('/%s', strstr($attachmentPath, 'uploads/')),
                'thumbnailUrl' => sprintf(
                    '/%s',
                    strstr(sprintf('%s/preview-%s', $this->issueAttachmentDirectory, $attachment->id), 'uploads/')
                ),
            ];
        }

        $issue->customAttachments = $issueAttachment;

        return $issue;
    }
}
