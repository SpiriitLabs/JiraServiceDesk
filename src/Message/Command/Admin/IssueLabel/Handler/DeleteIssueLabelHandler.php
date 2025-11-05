<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\IssueLabel\Handler;

use App\Controller\Common\DeleteControllerTrait;
use App\Entity\IssueLabel;
use App\Message\Command\Admin\IssueLabel\DeleteIssueLabel;
use App\Message\Command\Common\DeleteEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteIssueLabelHandler
{
    use DeleteControllerTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(DeleteIssueLabel $command): void
    {
        $issueLabel = $command->issueLabel;

        $users = $issueLabel->getUsers();
        foreach ($users as $user) {
            $issueLabel->removeUser($user);
        }
        $this->entityManager->flush();

        $this->handle(
            new DeleteEntity(
                class: IssueLabel::class,
                id: $issueLabel->getId(),
            ),
        );
    }
}
