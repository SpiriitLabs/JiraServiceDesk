<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\Controller\Common\GetControllerTrait;
use App\Entity\User;
use App\Message\Command\Admin\User\ExportUsers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/users/export',
    name: RouteCollection::EXPORT->value,
    methods: [Request::METHOD_GET],
)]
class ExportController extends AbstractController
{
    use GetControllerTrait;

    public function __invoke(
        Request $request,
        #[CurrentUser]
        User $user,
    ): Response {
        $csv = $this->handle(new ExportUsers());

        $response = new Response($csv);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'export_users.csv'
        );

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
