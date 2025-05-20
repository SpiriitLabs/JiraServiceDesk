<?php

namespace App\Message\Event\Webhook\Comment\Handler;

use App\Message\Command\Common\EmailNotification;
use App\Message\Event\Webhook\Comment\CommentUpdated;
use App\Repository\ProjectRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class CommentUpdatedHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly ProjectRepository $projectRepository,
        private readonly TranslatorInterface $translator,
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
        if ($project == null) {
            return;
        }

        $this->logger->info('WEBHOOK/CommentUpdated', [
            'issueKey' => $issueKey,
            'issueSummary' => $issueSummary,
            'projectId' => $project->getId(),
            'projectKey' => $project->jiraKey,
        ]);

        $templatedEmail = (new TemplatedEmail())
            ->htmlTemplate('email/comment/updated.html.twig')
            ->context([
                'project' => $project,
                'issueSummary' => $issueSummary,
                'issueKey' => $issueKey,
                'commentAuthorName' => $event->getPayload()['comment']['author']['displayName'],
                'commentAuthorAvatarUrl' => array_shift($event->getPayload()['comment']['author']['avatarUrls']),
                'commentBody' => $event->getPayload()['comment']['body'],
            ])
        ;

        foreach ($project->getUsers() as $user) {
            if ($user->preferenceNotificationCommentUpdated === false) {
                continue;
            }

            $emailToSent = clone $templatedEmail
                ->subject(
                    $this->translator->trans(
                        id: 'comment.created.title',
                        domain: 'email',
                        locale: $user->preferredLocale->value,
                    ),
                )
                ->to(new Address($user->email, $user->fullName))
                ->locale($user->preferredLocale->value)
            ;

            $this->logger->info('WEBHOOK/CommentUpdated - Generate mail to user', [
                'user' => $user->email,
            ]);
            $this->commandBus->dispatch(
                new EmailNotification(
                    user: $user,
                    email: $emailToSent,
                ),
            );
        }
    }
}
