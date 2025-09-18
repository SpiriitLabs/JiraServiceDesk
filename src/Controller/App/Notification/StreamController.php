<?php

namespace App\Controller\App\Notification;

use App\Controller\Common\CreateControllerTrait;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\UX\Turbo\TurboBundle;

#[Route(
    path: '/notification/stream',
    name: RouteCollection::NOTIFICATION_STREAM->value,
)]
class StreamController extends AbstractController
{
    use CreateControllerTrait;

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
    ) {
    }

    public function __invoke(
        Request $request,
        #[CurrentUser]
        User $user,
    ): Response {
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        $notifications = $this->notificationRepository->getLastsByUser($user);

        return $this->render(
            view: 'components/app/notification/list.html.twig',
            parameters: [
                'notifications' => $notifications,
            ],
        );
    }
}
