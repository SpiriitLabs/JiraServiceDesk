<?php

namespace App\Message\Event\Webhook\Issue\Handler;

use App\Entity\Favorite;
use App\Message\Command\Common\DeleteEntity;
use App\Message\Event\Webhook\Issue\IssueDeleted;
use App\Repository\FavoriteRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class IssueDeletedHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly FavoriteRepository $favoriteRepository,
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
    }
}
