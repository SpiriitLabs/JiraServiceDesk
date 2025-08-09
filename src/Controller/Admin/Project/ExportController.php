<?php

namespace App\Controller\Admin\Project;

use App\Controller\Common\GetControllerTrait;
use App\Message\Command\Admin\Project\ExportProjects;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/projects/export',
    name: RouteCollection::EXPORT->value,
    methods: [Request::METHOD_GET],
)]
class ExportController
{
    use GetControllerTrait;

    public function __invoke(
        Request $request,
    ): Response {
        $csv = $this->handle(new ExportProjects());

        $response = new Response($csv);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'export_projects.csv'
        );

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
