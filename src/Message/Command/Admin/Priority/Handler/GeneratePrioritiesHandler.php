<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\Priority\Handler;

use App\Entity\Priority;
use App\Message\Command\Admin\Priority\GeneratePriorities;
use App\Repository\Jira\PriorityRepository as JiraPriorityRepository;
use App\Repository\PriorityRepository as BDDPriorityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GeneratePrioritiesHandler
{
    public function __construct(
        private JiraPriorityRepository $jiraPriorityRespository,
        private BDDPriorityRepository $priorityRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(GeneratePriorities $command): void
    {
        $existingPriorities = $this->priorityRepository->findAll();
        foreach ($existingPriorities as $priority) {
            $this->entityManager->remove($priority);
        }

        $jiraPriorities = $this->jiraPriorityRespository->getAll();
        foreach ($jiraPriorities as $jiraPriority) {
            /** @var \JiraCloud\Issue\Priority $jiraPriority */
            $priority = new Priority(
                name: $jiraPriority->name,
                description: $jiraPriority->description,
                jiraId: (int) ($jiraPriority->id),
                iconUrl: $jiraPriority->iconUrl,
                statusColor: $jiraPriority->statusColor,
            );

            $this->entityManager->persist($priority);
        }
    }
}
