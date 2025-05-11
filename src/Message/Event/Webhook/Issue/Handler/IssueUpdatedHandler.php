<?php

namespace App\Message\Event\Webhook\Issue\Handler;

use App\Message\Command\Common\EmailNotification;
use App\Message\Event\Webhook\Issue\IssueUpdated;
use App\Repository\ProjectRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class IssueUpdatedHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly ProjectRepository $projectRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(IssueUpdated $event): void
    {
        $issueKey = $event->getPayload()['issue']['key'];
        $issueSummary = $event->getPayload()['issue']['fields']['summary'];
        $project = $this->projectRepository->findOneBy([
            'jiraId' => $event->getPayload()['issue']['fields']['project']['id'],
            'jiraKey' => $event->getPayload()['issue']['fields']['project']['key'],
        ]);
        $this->logger->info('WEBHOOK/IssueUpdated', [
            'issueKey' => $issueKey,
            'issueSummary' => $issueSummary,
            'projectId' => $project->getId(),
            'projectKey' => $project->jiraKey,
        ]);

        $templatedEmail = (new TemplatedEmail())
            ->subject(
                $this->translator->trans(
                    id: 'issue.edited.title',
                    domain: 'email',
                ),
            )
            ->htmlTemplate('email/issue/issue_edited.html.twig')
            ->context([
                'project' => $project,
                'issueSummary' => $issueSummary,
                'issueKey' => $issueKey,
            ])
        ;

        foreach ($project->getUsers() as $user) {
            if ($user->preferenceNotificationIssueUpdated === false) {
                continue;
            }

            $emailToSent = clone $templatedEmail
                ->to(new Address($user->email, $user->fullName))
                ->locale($user->preferredLocale->value)
            ;

            $this->logger->info('WEBHOOK/IssueUpdated - Generate mail to user', [
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
