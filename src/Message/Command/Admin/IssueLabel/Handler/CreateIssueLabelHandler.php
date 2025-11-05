<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\IssueLabel\Handler;

use App\Entity\IssueLabel;
use App\Message\Command\Admin\IssueLabel\CreateIssueLabel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateIssueLabelHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CreateIssueLabel $command): ?IssueLabel
    {
        $issueLabel = new IssueLabel(
            jiraLabel: $command->jiraLabel,
            name: $command->name,
        );
        if ($command->users) {
            foreach ($command->users as $user) {
                $issueLabel->addUser($user);
            }
        }
        $this->entityManager->persist($issueLabel);
        $this->entityManager->flush();

        return $issueLabel;
    }
}
