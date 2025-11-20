<?php

declare(strict_types=1);

namespace App\Message\Event\Webhook\Issue\Handler;

use App\Enum\Notification\NotificationType;
use App\Formatter\Jira\IssueHistoryFormatter;
use App\Message\Command\Common\Notification;
use App\Message\Event\Webhook\Issue\IssueUpdated;
use App\Repository\Jira\IssueRepository;
use App\Repository\ProjectRepository;
use JiraCloud\JiraException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class IssueUpdatedHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly ProjectRepository $projectRepository,
        private readonly TranslatorInterface $translator,
        private readonly IssueRepository $issueRepository,
        private readonly IssueHistoryFormatter $issueHistoryFormatter,
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(IssueUpdated $event): void
    {
        $issueKey = $event->getPayload()['issue']['key'];
        $issueId = $event->getPayload()['issue']['id'];
        $issueSummary = $event->getPayload()['issue']['fields']['summary'];
        $project = $this->projectRepository->findOneBy([
            'jiraId' => $event->getPayload()['issue']['fields']['project']['id'],
            'jiraKey' => $event->getPayload()['issue']['fields']['project']['key'],
        ]);
        if ($project == null) {
            return;
        }

        $this->logger->info('WEBHOOK/IssueUpdated', [
            'issueKey' => $issueKey,
            'issueSummary' => $issueSummary,
            'projectId' => $project->getId(),
            'projectKey' => $project->jiraKey,
        ]);
        $cache = new FilesystemAdapter();
        $cache->clear(sprintf('jira.full_issue_%s', $issueId));

        foreach ($project->getUsers() as $user) {
            if ($user->preferenceNotificationIssueUpdated === false) {
                continue;
            }

            try {
                $issue = $this->issueRepository->getFull($issueKey, $user->getJiraLabel());
            } catch (JiraException $jiraException) {
                return;
            }

            $templatedEmail = (new TemplatedEmail())
                ->htmlTemplate('email/issue/issue_edited.html.twig')
                ->context([
                    'project' => $project,
                    'issueSummary' => $issueSummary,
                    'issueKey' => $issueKey,
                    'changes' => $this->issueHistoryFormatter->format($issue),
                ])
            ;

            $subject = $this->translator->trans(
                id: 'issue.edited.title',
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

            $this->logger->info('WEBHOOK/IssueUpdated - Generate mail to user', [
                'user' => $user->email,
            ]);
            $link = $this->router->generate('browse_issue', [
                'keyIssue' => $issueKey,
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->commandBus->dispatch(
                new Notification(
                    user: $user,
                    email: $emailToSent,
                    notificationType: NotificationType::ISSUE_UPDATED,
                    subject: $subject,
                    body: $issueSummary,
                    link: $link,
                ),
            );
        }
    }
}
