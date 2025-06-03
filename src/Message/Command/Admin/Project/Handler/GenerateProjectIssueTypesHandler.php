<?php

namespace App\Message\Command\Admin\Project\Handler;

use App\Entity\IssueType;
use App\Message\Command\Admin\Project\GenerateProjectIssueTypes;
use App\Repository\Jira\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GenerateProjectIssueTypesHandler
{
    public function __construct(
        private ProjectRepository $jiraProjectRepository,
        private EntityManagerInterface $entityManager,
        #[Autowire(env: 'json:NOT_AVAILABLE_TYPES_JIRA_ID')]
        private array $notAvailableTypes,
    ) {
    }

    public function __invoke(GenerateProjectIssueTypes $command): void
    {
        $jiraIssueTypes = $command->jiraIssueTypes;
        $project = $command->project;

        if ($jiraIssueTypes == null) {
            $jiraProject = $this->jiraProjectRepository->get($project->jiraKey);
            $jiraIssueTypes = $jiraProject->issueTypes;
        }

        foreach ($jiraIssueTypes as $issueType) {
            if (in_array($issueType->id, $this->notAvailableTypes)) {
                continue;
            }

            $issueType = new IssueType(
                jiraId: $issueType->id,
                name: $issueType->name,
                description: $issueType->description,
                iconUrl: $issueType->iconUrl,
            );

            $this->entityManager->persist($issueType);
            $project = $project->addIssuesType($issueType);
        }
    }
}
