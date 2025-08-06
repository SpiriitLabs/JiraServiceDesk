<?php

namespace App\Message\Command\App\Notification\Handler;

use App\Controller\App\RouteCollection;
use App\Message\Command\App\Notification\SendPushNotification;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\RouterInterface;

#[AsMessageHandler]
readonly class SendPushNotificationHandler
{

    public function __construct(
        #[Autowire(env: 'VAPID_PUBLIC_KEY')]
        private string          $vapidPublicKey,
        #[Autowire(env: 'VAPID_PRIVATE_KEY')]
        private string          $vapidPrivateKey,
        private RouterInterface $router,
    ) {
    }

    public function __invoke(SendPushNotification $command): void
    {
        $subscription = Subscription::create([
            'endpoint' => $command->user->pushNotificationInfo->endpoint,
            'publicKey' => $command->user->pushNotificationInfo->p256dh,
            'authToken' => $command->user->pushNotificationInfo->auth,
        ]);

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => $this->router->generate(RouteCollection::PROJECT_SELECT->prefixed()),
                'publicKey' => $this->vapidPublicKey,
                'privateKey' => $this->vapidPrivateKey,
            ],
        ]);

        $payload = json_encode($command->notification->toArray());
        try {
            $webPush->sendOneNotification($subscription, $payload);
        } catch (\Throwable $exception) {
            dd($exception->getMessage());
        }
    }

}
