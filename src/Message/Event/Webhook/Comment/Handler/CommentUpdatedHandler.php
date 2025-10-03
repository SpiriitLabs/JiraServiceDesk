<?php

namespace App\Message\Event\Webhook\Comment\Handler;

use App\Enum\Notification\NotificationType;
use App\Message\Command\Common\Notification;
use App\Message\Event\Webhook\Comment\CommentUpdated;
use App\Repository\Jira\IssueRepository;
use App\Repository\ProjectRepository;
use App\Service\ReplaceAccountIdByDisplayName;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class CommentUpdatedHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly ProjectRepository $projectRepository,
        private readonly TranslatorInterface $translator,
        private readonly IssueRepository $issueRepository,
        private readonly ReplaceAccountIdByDisplayName $replaceAccountIdByDisplayName,
        #[Autowire(env: 'JIRA_ACCOUNT_ID')]
        private string $jiraAPIAccountId,
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(CommentUpdated $event): void
    {
        $issueKey = $event->getPayload()['issue']['key'];
        $issueSummary = $event->getPayload()['issue']['fields']['summary'];
        $project = $this->projectRepository->findOneBy([
            'jiraId' => $event->getPayload()['issue']['fields']['project']['id'],
            'jiraKey' => $event->getPayload()['issue']['fields']['project']['key'],
        ]);
        $comment = $this->issueRepository->getComment($issueKey, $event->getPayload()['comment']['id']);
        if ($project == null) {
            return;
        }

        if ($comment->visibility !== null) {
            return;
        }

        $this->logger->info('WEBHOOK/CommentUpdated', [
            'issueKey' => $issueKey,
            'issueSummary' => $issueSummary,
            'projectId' => $project->getId(),
            'projectKey' => $project->jiraKey,
        ]);

        $commentBody = $event->getPayload()['comment']['body'];
        $commentBody = $this->replaceAccountIdByDisplayName->replaceInCommentBody($commentBody);
        $templatedEmail = (new TemplatedEmail())
            ->htmlTemplate('email/comment/updated.html.twig')
            ->context([
                'project' => $project,
                'issueSummary' => $issueSummary,
                'issueKey' => $issueKey,
                'commentAuthorName' => $event->getPayload()['comment']['updateAuthor']['displayName'],
                'commentAuthorAvatarUrl' => array_shift($event->getPayload()['comment']['updateAuthor']['avatarUrls']),
                'commentBody' => $commentBody,
            ])
        ;

        foreach ($project->getUsers() as $user) {
            if (
                $user->preferenceNotificationCommentUpdated === false
                && (
                    $user->preferenceNotificationCommentOnlyOnTag == false
                    || ! str_contains(
                        haystack: mb_strtolower($event->getPayload()['comment']['body']),
                        needle: sprintf(
                            '[~accountid:%s]',
                            $this->jiraAPIAccountId,
                        ),
                    )
                )
            ) {
                continue;
            }

            $subject = $this->translator->trans(
                id: 'comment.updated.title',
                parameters: [
                    '%project_name%' => $project->name,
                    '%ticket_name%' => $issueSummary,
                ],
                domain: 'email',
                locale: $user->preferredLocale->value,
            );

            $emailToSent = clone $templatedEmail
                ->subject($subject)
                ->to(new Address($user->email, $user->getFullName()))
                ->locale($user->preferredLocale->value)
            ;

            $this->logger->info('WEBHOOK/CommentUpdated - Generate mail to user', [
                'user' => $user->email,
            ]);

            $link = $this->router->generate('browse_issue', [
                'keyIssue' => $issueKey,
                'focusedCommentId' => $comment->id,
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->commandBus->dispatch(
                new Notification(
                    user: $user,
                    email: $emailToSent,
                    notificationType: NotificationType::COMMENT_UPDATED,
                    subject: $subject,
                    body: $commentBody,
                    link: $link,
                ),
            );
        }
    }
}
