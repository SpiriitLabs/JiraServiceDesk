<?php

namespace App\Controller\App;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/dashboard',
    name: RouteCollection::DASHBOARD->value,
    methods: [Request::METHOD_GET]
)]
class DashboardController extends AbstractController
{

    public function __invoke(): Response
    {
        return $this->render(
            view: 'app/dashboard.html.twig',
        );
    }

}
