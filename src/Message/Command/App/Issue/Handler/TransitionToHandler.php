<?php

namespace App\Message\Command\App\Issue\Handler;

use App\Message\Command\App\Issue\TransitionTo;
use App\Repository\Jira\IssueRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class TransitionToHandler
{
    public function __construct(
        private IssueRepository $issueRepository,
    ) {
    }

    public function __invoke(TransitionTo $command): void
    {
        $this->issueRepository->transitionTo(
            id: $command->issueId,
            transitionId: $command->transitionId,
        );
    }
}
