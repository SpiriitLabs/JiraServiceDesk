<?php

declare(strict_types=1);

namespace App\Subscriber;

use App\Entity\User;
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
        $userIdentifier = $event->userIdentifier();

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $userIdentifier,
        ]);

        $this->dispatcher->dispatch(
            new NotificationEvent(
                user: $user,
                message: sprintf('New device login email sent to "%s"', $user->email),
            ),
            NotificationEvent::EVENT_NAME,
        );
    }
}
