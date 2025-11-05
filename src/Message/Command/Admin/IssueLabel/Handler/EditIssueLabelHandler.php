<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\IssueLabel\Handler;

use App\Entity\IssueLabel;
use App\Message\Command\Admin\IssueLabel\EditIssueLabel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class EditIssueLabelHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(EditIssueLabel $command): ?IssueLabel
    {
        dump($command);
        $issueLabel = $command->issueLabel;
        if ($command->users) {
            foreach ($issueLabel->getUsers() as $issueLabelUser) {
                $issueLabel->removeUser($issueLabelUser);
            }
            foreach ($command->users as $user) {
                $issueLabel->addUser($user);
            }
        }
        $issueLabel->jiraLabel = $command->jiraLabel;
        $issueLabel->name = $command->name;
        dump($issueLabel);
        $this->entityManager->persist($issueLabel);

        return $issueLabel;
    }
}
