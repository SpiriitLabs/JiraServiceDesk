<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserAuthenticationLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Spiriit\Bundle\AuthLogBundle\AuthenticationLog\AuthenticationLogCreatorInterface;
use Spiriit\Bundle\AuthLogBundle\Entity\AbstractAuthenticationLog;
use Spiriit\Bundle\AuthLogBundle\FetchUserInformation\UserInformation;
use Spiriit\Bundle\AuthLogBundle\Repository\AuthenticationLogRepositoryInterface;

/**
 * @extends ServiceEntityRepository<UserAuthenticationLog>
 */
class UserAuthenticationLogRepository extends ServiceEntityRepository implements AuthenticationLogRepositoryInterface, AuthenticationLogCreatorInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAuthenticationLog::class);
    }

    public function save(AbstractAuthenticationLog $log): void
    {
        $this->getEntityManager()
            ->persist($log)
        ;
        $this->getEntityManager()
            ->flush()
        ;
    }

    public function findExistingLog(string $userIdentifier, UserInformation $userInformation): bool
    {
        return (bool) $this->createQueryBuilder('uu')
            ->innerJoin('uu.user', 'u')
            ->andWhere('uu.ipAddress = :ip')
            ->andWhere('uu.userAgent = :ua')
            ->andWhere('u.email = :email')
            ->setParameter('email', $userIdentifier)
            ->setParameter('ip', $userInformation->ipAddress)
            ->setParameter('ua', $userInformation->userAgent)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() ?? false
        ;
    }

    public function createLog(string $userIdentifier, UserInformation $userInformation): AbstractAuthenticationLog
    {
        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy([
                'email' => $userIdentifier,
            ])
        ;

        if (! $user instanceof User) {
            throw new \InvalidArgumentException(sprintf('User with email "%s" not found.', $userIdentifier));
        }

        return new UserAuthenticationLog(
            user: $user,
            userInformation: $userInformation,
        );
    }
}
