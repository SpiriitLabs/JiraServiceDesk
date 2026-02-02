<?php

declare(strict_types=1);

namespace App\Message\Event\Webhook\Issue\Handler;

use App\Enum\Notification\NotificationChannel;
use App\Enum\Notification\NotificationType;
use App\Message\Command\Common\Notification;
use App\Message\Event\Webhook\Issue\IssueCreated;
use App\Repository\Jira\IssueRepository;
use App\Repository\ProjectRepository;
use JiraCloud\JiraException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class IssueCreatedHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly ProjectRepository $projectRepository,
        private readonly TranslatorInterface $translator,
        private readonly IssueRepository $issueRepository,
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(IssueCreated $event): void
    {
        $issueKey = $event->getPayload()['issue']['key'];
        $issueSummary = $event->getPayload()['issue']['fields']['summary'];
        $assignee = $event->getPayload()['issue']['fields']['assignee']['displayName'] ?? null;
        $project = $this->projectRepository->findOneBy([
            'jiraId' => $event->getPayload()['issue']['fields']['project']['id'],
            'jiraKey' => $event->getPayload()['issue']['fields']['project']['key'],
        ]);
        if ($project == null) {
            return;
        }

        $this->logger->info('WEBHOOK/IssueCreated', [
            'issueKey' => $issueKey,
            'issueSummary' => $issueSummary,
            'projectId' => $project->getId(),
            'projectKey' => $project->jiraKey,
        ]);

        $issueLabels = $event->getPayload()['issue']['fields']['labels'] ?? [];

        $templatedEmail = (new TemplatedEmail())
            ->htmlTemplate('email/issue/issue_created.html.twig')
            ->context([
                'project' => $project,
                'issueSummary' => $issueSummary,
                'issueKey' => $issueKey,
            ])
        ;

        foreach ($project->getUsers() as $user) {
            $channels = $user->preferenceNotificationIssueCreated;
            if ($channels === []) {
                continue;
            }

            if ($user->hasAnyJiraLabel($issueLabels) === false) {
                continue;
            }

            try {
                $this->issueRepository->getFull($issueKey, $user->getJiraLabels());
            } catch (JiraException $jiraException) {
                continue;
            }

            $subject = $this->translator->trans(
                id: 'issue.created.title',
                parameters: [
                    '%project_name%' => $project->name,
                ],
                domain: 'email',
                locale: $user->preferredLocale->value,
            );

            $emailToSent = null;
            if (in_array(NotificationChannel::EMAIL, $channels, true)) {
                $emailToSent = clone $templatedEmail
                    ->subject($subject)
                    ->to(new Address($user->email, $user->getFullName()))
                    ->locale($user->preferredLocale->value)
                ;
            }

            $this->logger->info('WEBHOOK/IssueCreated - Generate mail to user', [
                'user' => $user->email,
            ]);
            $link = $this->router->generate('browse_issue', [
                'keyIssue' => $issueKey,
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $slackExtraContext = [];
            if ($assignee !== null) {
                $assigneeLabel = $this->translator->trans(
                    id: 'issue.assignee.label',
                    domain: 'app',
                    locale: $user->preferredLocale->value,
                );
                $slackExtraContext[$assigneeLabel] = $assignee;
            }

            $this->commandBus->dispatch(
                new Notification(
                    user: $user,
                    email: $emailToSent,
                    notificationType: NotificationType::ISSUE_CREATED,
                    subject: $subject,
                    body: $issueSummary,
                    link: $link,
                    channels: $channels,
                    slackExtraContext: $slackExtraContext,
                ),
            );
        }
    }
}
