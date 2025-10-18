<?php

namespace App\Subscriber;

use App\Entity\User;
use App\Entity\UserAuthenticationLog;
use App\Subscriber\Event\NotificationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Spiriit\Bundle\AuthLogBundle\Listener\AuthenticationLogEvent;
use Spiriit\Bundle\AuthLogBundle\Listener\AuthenticationLogEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserAuthenticationLogSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
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
            user: $this->entityManager->getRepository(User::class)->findOneBy([
                'id' => $userReference->id,
            ]),
            userInformation: $userInfo
        );
        $this->entityManager->persist($userAuthenticationLog);
        $this->entityManager->flush();

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'id' => $userReference->id,
        ]);
        $this->dispatcher->dispatch(
            new NotificationEvent(
                user: $user,
                message: sprintf('New device login email sent to "%s"', $user->email),
            ),
            NotificationEvent::EVENT_NAME,
        );

        $event->markAsHandled();
    }
}
