<?php

namespace App\Security\AuthenticationLog;

use App\Entity\User;
use App\Entity\UserAuthenticationLog;
use Doctrine\ORM\EntityManagerInterface;
use Spiriit\Bundle\AuthLogBundle\AuthenticationLogFactory\AuthenticationLogFactoryInterface;
use Spiriit\Bundle\AuthLogBundle\DTO\UserReference;
use Spiriit\Bundle\AuthLogBundle\FetchUserInformation\UserInformation;

class UserAuthenticationLogFactory implements AuthenticationLogFactoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function supports(): string
    {
        return 'user';
    }

    public function createUserReference(string $userIdentifier): UserReference
    {
        $realCustomer = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $userIdentifier,
        ]);

        if (! $realCustomer instanceof User) {
            throw new \InvalidArgumentException();
        }

        return new UserReference(
            type: 'user',
            id: (string) $realCustomer->getId(),
        );
    }

    public function isKnown(
        UserReference $userReference,
        UserInformation $userInformation,
    ): bool {
        return (bool) $this->entityManager->createQueryBuilder()
            ->select('uu')
            ->from(UserAuthenticationLog::class, 'uu')
            ->innerJoin('uu.user', 'u')
            ->andWhere('uu.ipAddress = :ip')
            ->andWhere('uu.userAgent = :ua')
            ->andWhere('u.id = :user_id')
            ->setParameter('user_id', $userReference->id)
            ->setParameter('ip', $userInformation->ipAddress)
            ->setParameter('ua', $userInformation->userAgent)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() ?? false
        ;
    }
}
