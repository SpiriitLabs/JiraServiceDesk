<?php

namespace App\Controller\Admin\User;

use App\Controller\Common\GetControllerTrait;
use App\Entity\User;
use App\Message\Command\Admin\User\ExportUsers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $csv = $this->handle(new ExportUsers(user: $user));

        $this->addFlash(
            type: 'success',
            message: 'user.flashes.exported',
        );

        return $this->redirectToRoute(RouteCollection::LIST->prefixed());
    }
}
