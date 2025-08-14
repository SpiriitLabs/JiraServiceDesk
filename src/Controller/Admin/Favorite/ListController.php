<?php

namespace App\Controller\Admin\Favorite;

use App\Controller\Common\GetControllerTrait;
use App\Entity\Favorite;
use App\Message\Query\PaginateEntities;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/favorites',
    name: RouteCollection::LIST->value,
    methods: [Request::METHOD_GET],
)]
class ListController extends AbstractController
{
    use GetControllerTrait;

    public function __invoke(
        Request $request,
    ): Response {
        $pagination = $this->handle(
            new PaginateEntities(
                class: Favorite::class,
                sort: $request->get('_sort', 'id'),
                page: $request->get('page', 1),
            ),
        );

        return $this->render(
            view: 'admin/favorite/list.html.twig',
            parameters: [
                'pagination' => $pagination,
            ],
        );
    }
}
