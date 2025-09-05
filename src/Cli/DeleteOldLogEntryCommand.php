<?php

declare(strict_types=1);

namespace App\Cli;

use App\Entity\LogEntry;
use App\Message\Command\Common\DeleteEntity;
use App\Repository\LogEntryRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(name: 'app:delete-old-log-entry')]
class DeleteOldLogEntryCommand extends Command
{
    use HandleTrait;

    private const string DELAY_EXPIRE = '- 7 days';

    public function __construct(
        private readonly LogEntryRepository $logEntryRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $currentDate = new \DateTimeImmutable();
        $expireDate = $currentDate->add(\DateInterval::createFromDateString(self::DELAY_EXPIRE));

        $qb = $this->logEntryRepository->createQueryBuilder('l');
        $logs = $qb
            ->where('l.logAt < = :current')
            ->setParameter(
                'current',
                $expireDate
            )
            ->getQuery()
            ->getResult()
        ;

        foreach ($logs as $logEntry) {
            $this->handle(new DeleteEntity(
                class: LogEntry::class,
                id: $logEntry->getId(),
            ));
        }

        $io->success(sprintf('Remove %s entities', count($logs)));

        return self::SUCCESS;
    }

    #[Required]
    public function setMessageBus(MessageBusInterface $queryBus): void
    {
        $this->messageBus = $queryBus;
    }
}
