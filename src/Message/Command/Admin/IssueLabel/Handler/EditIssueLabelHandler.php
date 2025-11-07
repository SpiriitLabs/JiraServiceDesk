<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\IssueLabel\Handler;

use App\Entity\IssueLabel;
use App\Exception\Project\IssueLabelNotValidException;
use App\Message\Command\Admin\IssueLabel\EditIssueLabel;
use App\Repository\Jira\LabelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class EditIssueLabelHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LabelRepository $labelRepository,
    ) {
    }

    public function __invoke(EditIssueLabel $command): ?IssueLabel
    {
        $jiraLabels = $this->labelRepository->getAll();
        if (! in_array($command->jiraLabel, $jiraLabels->getValues())) {
            throw new IssueLabelNotValidException();
        }

        $issueLabel = $command->issueLabel;
        foreach ($issueLabel->getUsers() as $issueLabelUser) {
            $issueLabel->removeUser($issueLabelUser);
        }
        if ($command->users) {
            foreach ($command->users as $user) {
                $issueLabel->addUser($user);
            }
        }
        $issueLabel->jiraLabel = $command->jiraLabel;
        $issueLabel->name = $command->name;
        $this->entityManager->persist($issueLabel);
        $this->entityManager->flush();

        return $issueLabel;
    }
}
