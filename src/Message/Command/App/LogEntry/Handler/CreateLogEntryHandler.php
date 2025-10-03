<?php

namespace App\Message\Command\App\LogEntry\Handler;

use App\Entity\LogEntry;
use App\Message\Command\App\LogEntry\CreateLogEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateLogEntryHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CreateLogEntry $command): ?LogEntry
    {
        $logEntry = new LogEntry(
            type: $command->type,
            subject: $command->subject,
            datas: $command->datas,
            level: $command->level,
        );

        $this->entityManager->persist($logEntry);
        $this->entityManager->flush();

        return $logEntry;
    }
}
