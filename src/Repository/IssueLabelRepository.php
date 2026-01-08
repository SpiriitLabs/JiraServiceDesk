<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IssueLabel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IssueLabel>
 */
class IssueLabelRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IssueLabel::class);
    }

    /**
     * @return string[]
     */
    public function getAllJiraLabels(): array
    {
        return $this->createQueryBuilder('il')
            ->select('il.jiraLabel')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }
}
