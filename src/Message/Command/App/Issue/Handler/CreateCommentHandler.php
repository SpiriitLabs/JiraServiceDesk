<?php

namespace App\Message\Command\App\Issue\Handler;

use App\Message\Command\App\Issue\CreateComment;
use App\Repository\Jira\IssueRepository;
use DH\Adf\Node\Block\Document;
use JiraCloud\Issue\Comment;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateCommentHandler
{

    public function __construct(
        private readonly IssueRepository $issueRepository,
    ) {
    }

    public function __invoke(CreateComment $command): void
    {
        $issue = $command->issue;
        $commentBody = $command->comment;
        $commentAttachments = $command->attachments;
        dd($commentAttachments);

        $this->issueRepository->createComment(
            id: $issue->id,
            comment: new Comment()->setBodyByAtlassianDocumentFormat(
                new Document()->paragraph()->text($commentBody)->end(),
            ),
        );

        foreach ($commentAttachments as $commentAttachment) {
            $this->issueRepository->createAttachment(
                id: $issue->id,
                filePath: $commentAttachment->filePath,
            );
        }
    }

}
