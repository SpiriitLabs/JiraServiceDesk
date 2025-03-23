<?php

namespace App\Controller\Admin\Project;

use App\Controller\Common\GetControllerTrait;
use App\Entity\Project;
use App\Message\Query\PaginateEntities;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/projects',
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
                Project::class,
                $request->get('_sort', 'id'),
                $request->get('page', 1),
            ),
        );

        return $this->render(
            view: 'admin/project/list.html.twig',
            parameters: [
                'pagination' => $pagination,
            ],
        );
    }
}
