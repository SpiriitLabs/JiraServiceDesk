<?php

declare(strict_types=1);

namespace App\Subscriber;

use App\Entity\User;
use App\Enum\LogEntry\Type;
use App\Subscriber\Event\NotificationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class LastLoginEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()
            ->getUser()
        ;

        if ($user instanceof User) {
            $user->setLastLoginAt(new \DateTimeImmutable());

            $this->entityManager->flush();

            $this->dispatcher->dispatch(
                new NotificationEvent(
                    user: $user,
                    message: sprintf('New Login successfully for "%s"', $user->email),
                    type: Type::LOGIN,
                ),
                NotificationEvent::EVENT_NAME,
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }
}
