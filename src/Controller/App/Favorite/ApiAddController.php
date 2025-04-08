<?php

namespace App\Controller\App\Favorite;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/api/favorite/add',
    name: RouteCollection::API_ADD_FAVORITE->value,
    methods: [Request::METHOD_POST],
)]
class ApiAddController extends AbstractController
{

    public function __invoke(): Response
    {
        return new Response('', 200);
    }

}
