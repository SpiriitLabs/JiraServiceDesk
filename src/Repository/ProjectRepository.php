<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use App\Model\Filter\ProjectFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function getByUser(User $user): QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS)
            ->innerJoin(self::ALIAS . '.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
        ;
    }

    public function filter(ProjectFilter $filter): QueryBuilder
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        if ($filter->user !== null) {
            $qb = $this->getByUser($filter->user);
        }

        if ($filter->query !== null) {
            $value = \sprintf('%%%s%%', $filter->query);
            $expr = $qb->expr();

            $qb->andWhere(
                $qb->expr()
                    ->orX(
                        $qb->expr()
                            ->like(self::ALIAS . '.jiraKey', $qb->expr()->literal($value)),
                        $qb->expr()
                            ->like(self::ALIAS . '.name', $qb->expr()->literal($value)),
                        $qb->expr()
                            ->like(self::ALIAS . '.description', $qb->expr()->literal($value)),
                    )
            );
        }

        return $qb;
    }
}
