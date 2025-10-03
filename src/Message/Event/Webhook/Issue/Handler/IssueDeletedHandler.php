<?php

namespace App\Message\Event\Webhook\Issue\Handler;

use App\Entity\Favorite;
use App\Enum\Notification\NotificationType;
use App\Message\Command\Common\DeleteEntity;
use App\Message\Command\Common\Notification;
use App\Message\Event\Webhook\Issue\IssueDeleted;
use App\Repository\FavoriteRepository;
use App\Repository\ProjectRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class IssueDeletedHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly ProjectRepository $projectRepository,
        private readonly TranslatorInterface $translator,
        private readonly FavoriteRepository $favoriteRepository,
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(IssueDeleted $event): void
    {
        $issueKey = $event->getPayload()['issue']['key'];
        $favorites = $this->favoriteRepository
            ->createQueryBuilder('favorite')
            ->where('favorite.code LIKE :key')
            ->setParameter('key', '%-favorite-issue-' . $issueKey)
            ->getQuery()
            ->getResult()
        ;
        foreach ($favorites as $favorite) {
            $this->commandBus->dispatch(
                new DeleteEntity(
                    class: Favorite::class,
                    id: $favorite->getId(),
                ),
            );
        }

        $issueSummary = $event->getPayload()['issue']['fields']['summary'];
        $project = $this->projectRepository->findOneBy([
            'jiraId' => $event->getPayload()['issue']['fields']['project']['id'],
            'jiraKey' => $event->getPayload()['issue']['fields']['project']['key'],
        ]);
        if ($project == null) {
            return;
        }

        foreach ($project->getUsers() as $user) {
            if ($user->preferenceNotificationIssueUpdated === false) {
                continue;
            }

            $subject = $this->translator->trans(
                id: 'issue.deleted.title',
                parameters: [
                    '%project_name%' => $project->name,
                    '%ticket_name%' => $issueSummary,
                ],
                domain: 'email',
                locale: $user->preferredLocale->value,
            );

            $link = $this->router->generate('app_project_view', [
                'key' => $project->jiraKey,
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->commandBus->dispatch(
                new Notification(
                    user: $user,
                    notificationType: NotificationType::ISSUE_DELETED,
                    subject: $subject,
                    body: $issueSummary,
                    link: $link,
                )
            );
        }
    }
}
