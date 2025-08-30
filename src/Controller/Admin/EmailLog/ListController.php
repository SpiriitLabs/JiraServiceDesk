<?php

namespace App\Controller\Admin\EmailLog;

use App\Controller\Common\GetControllerTrait;
use App\Entity\EmailLog;
use App\Form\Filter\EmailLogFormFilter;
use App\Message\Query\PaginateEntities;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/email-log',
    name: RouteCollection::LIST->value,
    methods: [Request::METHOD_GET],
)]
class ListController extends AbstractController
{
    use GetControllerTrait;

    public function __invoke(
        Request $request,
    ): Response {
        $filterForm = $this->createForm(EmailLogFormFilter::class);
        $filterForm->handleRequest($request);

        $pagination = $this->handle(
            new PaginateEntities(
                class: EmailLog::class,
                sort: $request->get('_sort', 'id'),
                page: $request->get('page', 1),
                form: $filterForm,
            ),
        );

        return $this->render(
            view: 'admin/email_log/list.html.twig',
            parameters: [
                'pagination' => $pagination,
                'filterForm' => $filterForm,
            ],
        );
    }
}
