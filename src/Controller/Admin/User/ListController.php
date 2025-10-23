<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\Controller\Common\GetControllerTrait;
use App\Controller\Traits\PaginationPerPageTrait;
use App\Entity\User;
use App\Form\Filter\UserFormFilter;
use App\Message\Query\PaginateEntities;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/users',
    name: RouteCollection::LIST->value,
    methods: [Request::METHOD_GET],
)]
class ListController extends AbstractController
{
    use GetControllerTrait;
    use PaginationPerPageTrait;

    public function __invoke(
        Request $request,
    ): Response {
        $filterForm = $this->createForm(UserFormFilter::class);
        $filterForm->handleRequest($request);

        $pagination = $this->handle(
            new PaginateEntities(
                class: User::class,
                sort: $request->get('_sort', 'id'),
                perPage: $request->get('perPage', 10),
                form: $filterForm,
            ),
        );
        $this->setCurrentPage($pagination, $request);

        return $this->render(
            view: 'admin/user/list.html.twig',
            parameters: [
                'pagination' => $pagination,
                'filterForm' => $filterForm,
            ],
        );
    }
}
