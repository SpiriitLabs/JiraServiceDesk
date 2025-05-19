<?php

namespace App\Message\Command\App\Issue\Handler;

use App\Controller\Common\CreateControllerTrait;
use App\Message\Command\App\Issue\AddAttachment;
use App\Message\Command\App\Issue\CreateComment;
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
            $originalFilename = $commentAttachment->getClientOriginalName();
            $tmpPath = sys_get_temp_dir() . '/' . $originalFilename;
            copy($commentAttachment->getPathname(), $tmpPath);
            $renamedFile = new UploadedFile(
                path: $tmpPath,
                originalName: $originalFilename,
                mimeType: $commentAttachment->getClientMimeType(),
                error: null,
                test: true
            );

            $attachments = array_merge(
                $attachments,
                $this->handle(
                    new AddAttachment(
                        issue: $issue,
                        file: $renamedFile,
                    ),
                )
            );
        }

        $commentDocumentBody = new Document()
            ->paragraph()
            ->text($commentBody)
            ->break()
            ->break()
        ;
        foreach ($attachments as $attachment) {
            /** @var Attachment $attachment */
            $commentDocumentBody = $commentDocumentBody
                ->link(
                    text: $attachment->filename,
                    href: $attachment->content,
                    title: $attachment->filename,
                )
                ->break()
            ;
        }
        $commentDocumentBody = $commentDocumentBody
            ->text('-------')
            ->break()
            ->text($command->user->fullName)
            ->end()
        ;

        $this->issueRepository->createComment(
            id: $issue->id,
            comment: new Comment()
                ->setBodyByAtlassianDocumentFormat($commentDocumentBody),
        );
    }
}
