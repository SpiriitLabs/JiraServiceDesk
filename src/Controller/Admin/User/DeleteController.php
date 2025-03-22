<?php

namespace App\Controller\Admin\User;

use App\Controller\Common\DeleteControllerTrait;
use App\Entity\User;
use App\Message\Command\Common\DeleteEntity;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/user/{id:user}/delete',
    name: RouteCollection::DELETE->value,
    methods: [Request::METHOD_DELETE],
)]
class DeleteController extends AbstractController
{
    use DeleteControllerTrait;

    public function __invoke(
        Request $request,
        #[MapEntity(mapping: [
            'id' => 'id',
        ])]
        User $user,
    ): RedirectResponse {
        $this->handle(
            new DeleteEntity(
                class: User::class,
                id: $user->getId(),
            ),
        );

        return $this->redirectToRoute(
            route: RouteCollection::LIST->prefixed(),
        );
    }
}
