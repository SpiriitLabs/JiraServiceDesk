<?php

namespace App\Message\Command\App\Issue\Handler;

use App\Message\Command\App\Issue\AddAttachment;
use App\Repository\Jira\IssueRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class AddAttachmentHandler
{
    public function __construct(
        private IssueRepository $issueRepository,
    ) {
    }

    public function __invoke(AddAttachment $command): array
    {
        return $this->issueRepository->createAttachment(
            id: $command->issue->id,
            filePath: $command->file->getRealPath(),
        );
    }
}
