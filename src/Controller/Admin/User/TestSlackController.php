<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\Entity\User;
use App\Service\SlackNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/user/{id:user}/test-slack',
    name: RouteCollection::TEST_SLACK->value,
    methods: [Request::METHOD_POST],
)]
class TestSlackController extends AbstractController
{
    public function __invoke(
        User $user,
        SlackNotificationService $slackNotificationService,
    ): Response {
        $result = $slackNotificationService->testConnection($user);

        if ($result === true) {
            $this->addFlash('success', 'user.slack.test.success');
        } else {
            $this->addFlash('danger', 'user.slack.test.error');
        }

        return $this->redirectToRoute(
            route: RouteCollection::EDIT->prefixed(),
            parameters: [
                'id' => $user->getId(),
            ],
        );
    }
}
