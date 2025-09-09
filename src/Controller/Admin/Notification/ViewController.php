<?php

namespace App\Controller\Admin\Notification;

use App\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/notification/{id}',
    name: RouteCollection::VIEW->value,
    methods: [Request::METHOD_GET],
)]
class ViewController extends AbstractController
{
    public function __invoke(
        Notification $notification,
    ): Response {
        return $this->render(
            view: 'admin/notification/view.html.twig',
            parameters: [
                'entity' => $notification,
            ],
        );
    }
}
