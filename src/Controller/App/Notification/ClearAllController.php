<?php

namespace App\Controller\App\Notification;

use App\Controller\App\Project\AbstractController;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/notification/clear-all',
    name: RouteCollection::NOTIFICATION_CLEAR_ALL->value,
    methods: [Request::METHOD_GET],
)]
class ClearAllController extends AbstractController
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser]
        User $user,
        Request $request,
    ): RedirectResponse {
        $this->notificationRepository->clearByUser($user);

        return $this->redirect($request->headers->get('referer'));
    }
}
