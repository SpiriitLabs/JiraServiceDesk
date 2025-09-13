<?php

declare(strict_types=1);

namespace App\Cli;

use App\Message\Event\Webhook\Comment\CommentCreated;
use App\Message\Event\Webhook\Comment\CommentUpdated;
use App\Message\Event\Webhook\Issue\IssueCreated;
use App\Message\Event\Webhook\Issue\IssueDeleted;
use App\Message\Event\Webhook\Issue\IssueUpdated;
use App\Model\SortParams;
use App\Repository\Jira\IssueRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

#[AsCommand(name: 'app:test-webhook-jira')]
class TestWebhookJiraCommand extends Command
{
    public function __construct(
        private readonly IssueRepository $issueRepository,
        private readonly MessageBusInterface $commandBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $issueKey = $io->ask('Issue key', null, Validation::createCallable(new NotBlank()));
        $type = $io->choice('Type de webhook ?', [
            'jira:issue_created',
            'jira:issue_updated',
            'jira:issue_deleted',
            'comment_created',
            'comment_updated',
        ]);

        $issue = $this->issueRepository->getFull($issueKey);
        $payload = [
            'issue' => [
                'key' => $issueKey,
                'fields' => [
                    'summary' => $issue->fields->summary,
                    'project' => [
                        'id' => $issue->fields->project->id,
                        'key' => $issue->fields->project->key,
                    ],
                ],
            ],
        ];

        if (
            $type === 'comment_created'
            || $type === 'comment_updated'
        ) {
            $comments = $this->issueRepository->getCommentForIssue(
                issueId: $issueKey,
                sort: new SortParams('created', '-')
            );
            $comments = $comments->comments;

            $commentIds = array_keys($comments);
            $commentId = $io->choice('Commentaire ?', $commentIds);
            $comment = $comments[$commentId];

            $payload['comment'] = [
                'id' => $commentId,
                'body' => $comment->renderedBody,
                'author' => [
                    'displayName' => $comment->author->displayName,
                    'avatarUrls' => $comment->author->avatarUrls,
                ],
                'updateAuthor' => [
                    'displayName' => $comment->updateAuthor->displayName,
                    'avatarUrls' => $comment->updateAuthor->avatarUrls,
                ],
            ];
        }

        $event = match ($type) {
            'jira:issue_created' => new IssueCreated(payload: $payload),
            'jira:issue_updated' => new IssueUpdated(payload: $payload),
            'jira:issue_deleted' => new IssueDeleted(payload: $payload),
            'comment_created' => new CommentCreated(payload: $payload),
            'comment_updated' => new CommentUpdated(payload: $payload),
        };

        $this->commandBus->dispatch(
            $event,
            [
                new DelayStamp(30),
            ]
        );

        return self::SUCCESS;
    }
}
