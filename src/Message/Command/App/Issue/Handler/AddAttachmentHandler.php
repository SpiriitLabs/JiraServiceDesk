<?php

namespace App\Message\Command\App\Issue\Handler;

use App\Message\Command\App\Issue\AddAttachment;
use App\Repository\Jira\IssueRepository;
use JiraCloud\Issue\Attachment;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class AddAttachmentHandler
{
    public function __construct(
        private IssueRepository $issueRepository,
    ) {
    }

    /**
     * @return array|Attachment[]
     */
    public function __invoke(AddAttachment $command): array
    {
        $originalFilename = $command->file->getClientOriginalName();
        $tmpPath = sys_get_temp_dir() . '/' . $originalFilename;
        copy($command->file->getPathname(), $tmpPath);
        $renamedFile = new UploadedFile(
            path: $tmpPath,
            originalName: $originalFilename,
            mimeType: $command->file->getClientMimeType(),
            error: null,
            test: true
        );

        return $this->issueRepository->createAttachment(
            id: $command->issue->id,
            filePath: $renamedFile->getRealPath(),
        );
    }
}
