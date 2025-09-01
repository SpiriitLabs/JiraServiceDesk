<?php

namespace App\Controller\Admin\LogEntry;

use App\Entity\LogEntry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/log-entry/{id}',
    name: RouteCollection::VIEW->value,
    methods: [Request::METHOD_GET],
)]
class ViewController extends AbstractController
{
    public function __invoke(
        LogEntry $logEntry,
    ): Response {
        return $this->render(
            view: 'admin/log_entry/view.html.twig',
            parameters: [
                'entity' => $logEntry,
            ],
        );
    }
}
