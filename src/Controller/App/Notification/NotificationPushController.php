<?php

namespace App\Controller\App\Notification;

use App\Controller\Common\CreateControllerTrait;
use App\Entity\User;
use App\Message\Command\App\Notification\RegisterUserPushNotificationInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/notification_push/register',
    name: RouteCollection::NOTIFICATION_REGISTER->value,
    methods: [Request::METHOD_POST],
)]
class NotificationPushController extends AbstractController
{
    use CreateControllerTrait;

    public function __invoke(
        Request $request,
        #[CurrentUser] User $user,
    ): Response {
        $payload = json_decode($request->getContent(), true);

        $this->handle(
            new RegisterUserPushNotificationInfo(
                user: $user,
                endpoint: $payload['endpoint'],
                p256dh: $payload['keys']['p256dh'] ?? null,
                auth: $payload['keys']['auth'] ?? null,
            ),
        );

        return new JsonResponse(data: [], status: Response::HTTP_CREATED);
    }

}
