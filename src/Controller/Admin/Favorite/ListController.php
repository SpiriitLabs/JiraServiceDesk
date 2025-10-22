<?php

declare(strict_types=1);

namespace App\Controller\Admin\Favorite;

use App\Controller\Common\GetControllerTrait;
use App\Entity\Favorite;
use App\Form\Filter\FavoriteFormFilter;
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
        $filterForm = $this->createForm(FavoriteFormFilter::class);
        $filterForm->handleRequest($request);

        $pagination = $this->handle(
            new PaginateEntities(
                class: Favorite::class,
                sort: $request->get('_sort', 'id'),
                page: $request->get('page', 1),
                form: $filterForm,
            ),
        );

        return $this->render(
            view: 'admin/favorite/list.html.twig',
            parameters: [
                'pagination' => $pagination,
                'filterForm' => $filterForm,
            ],
        );
    }
}
