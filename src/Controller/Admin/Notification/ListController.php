<?php

namespace App\Controller\Admin\Notification;

use App\Controller\Common\GetControllerTrait;
use App\Entity\Notification;
use App\Form\Filter\NotificationFormFilter;
use App\Message\Query\PaginateEntities;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/notifications',
    name: RouteCollection::LIST->value,
    methods: [Request::METHOD_GET],
)]
class ListController extends AbstractController
{
    use GetControllerTrait;

    public function __invoke(
        Request $request,
    ): Response {
        $filterForm = $this->createForm(NotificationFormFilter::class);
        $filterForm->handleRequest($request);

        $pagination = $this->handle(
            new PaginateEntities(
                class: Notification::class,
                sort: $request->get('_sort', '-id'),
                page: $request->get('page', 1),
                form: $filterForm,
            ),
        );

        return $this->render(
            view: 'admin/notification/list.html.twig',
            parameters: [
                'pagination' => $pagination,
                'filterForm' => $filterForm,
            ],
        );
    }
}
