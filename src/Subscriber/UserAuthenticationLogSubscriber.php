<?php

namespace App\Subscriber;

use App\Entity\User;
use App\Entity\UserAuthenticationLog;
use Doctrine\ORM\EntityManagerInterface;
use Spiriit\Bundle\AuthLogBundle\Listener\AuthenticationLogEvent;
use Spiriit\Bundle\AuthLogBundle\Listener\AuthenticationLogEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserAuthenticationLogSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationLogEvents::NEW_DEVICE => 'onLogin',
        ];
    }

    public function onLogin(AuthenticationLogEvent $event): void
    {
        $userReference = $event->getUserReference();
        $userInfo = $event->getUserInformation();

        $userAuthenticationLog = new UserAuthenticationLog(
            user: $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userReference->id]),
            userInformation: $userInfo
        );
        $this->entityManager->persist($userAuthenticationLog);
        $this->entityManager->flush();

        $event->markAsHandled();
    }
}
