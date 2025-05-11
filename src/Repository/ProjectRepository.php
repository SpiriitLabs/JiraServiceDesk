<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
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
}
