<?php

declare(strict_types=1);

namespace App\Controller\App\Notification;

use App\Controller\App\Project\AbstractController;
use App\Controller\Common\EditControllerTrait;
use App\Entity\Notification;
use App\Entity\User;
use App\Message\Command\App\Notification\NotificationViewed;
use JiraCloud\JiraException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/notification/{id}/viewed',
)]
class ViewedController extends AbstractController
{
    use EditControllerTrait;

    #[Route(
        path: '/api',
        name: RouteCollection::NOTIFICATION_API_VIEWED->value,
        methods: [Request::METHOD_POST],
    )]
    public function apiSetViewedNotification(
        #[CurrentUser]
        User $user,
        Notification $notification,
    ): Response {
        if ($notification->user !== $user) {
            throw $this->createNotFoundException();
        }

        try {
            $this->handle(
                new NotificationViewed(
                    notificationId: (string) $notification->getId(),
                ),
            );

            return new Response(status: Response::HTTP_OK);
        } catch (JiraException $exception) {
            return new Response(status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
