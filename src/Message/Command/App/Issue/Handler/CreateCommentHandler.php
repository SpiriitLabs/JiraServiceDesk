<?php

declare(strict_types=1);

namespace App\Message\Command\App\Issue\Handler;

use App\Controller\Common\CreateControllerTrait;
use App\Formatter\Jira\AdfHardBreakFormatter;
use App\Message\Command\App\Issue\AddAttachment;
use App\Message\Command\App\Issue\CreateComment;
use App\Message\Trait\AppendCreatorTrait;
use App\Repository\Jira\IssueRepository;
use DH\Adf\Node\Block\Document;
use JiraCloud\Issue\Attachment;
use JiraCloud\Issue\Comment;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateCommentHandler
{
    use CreateControllerTrait;
    use AppendCreatorTrait;

    public function __construct(
        private readonly IssueRepository $issueRepository,
    ) {
    }

    public function __invoke(CreateComment $command): void
    {
        $issue = $command->issue;
        $commentAttachments = $command->attachments;
        $commentBody = $command->comment;

        $attachments = [];
        foreach ($commentAttachments as $commentAttachment) {
            /** @var UploadedFile $commentAttachment */
            $attachments = array_merge(
                $attachments,
                $this->handle(
                    new AddAttachment(
                        issue: $issue,
                        file: $commentAttachment,
                    ),
                )
            );
        }

        $descriptionData = $this->appendCreator(
            $command->user,
            AdfHardBreakFormatter::format((array) json_decode($commentBody, true))
        );
        $commentDocumentBody = Document::load($descriptionData);
        foreach ($attachments as $attachment) {
            /** @var Attachment $attachment */
            $newParagraph = new Document()
                ->paragraph()
                ->link(
                    text: $attachment->filename,
                    href: $attachment->content,
                    title: $attachment->filename,
                )
                ->break()
            ;
            $commentDocumentBody
                ->append($newParagraph)
            ;
        }

        $this->issueRepository->createComment(
            id: $issue->id,
            comment: new Comment()
                ->setBodyByAtlassianDocumentFormat($commentDocumentBody),
        );
    }
}
