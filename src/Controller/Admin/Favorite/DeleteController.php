<?php

declare(strict_types=1);

namespace App\Controller\Admin\Favorite;

use App\Controller\Common\DeleteControllerTrait;
use App\Entity\Favorite;
use App\Message\Command\Common\DeleteEntity;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/favorite/{id}/delete',
    name: RouteCollection::DELETE->value,
    methods: [Request::METHOD_POST],
)]
class DeleteController extends AbstractController
{
    use DeleteControllerTrait;

    public function __invoke(
        Request $request,
        #[MapEntity(mapping: [
            'id' => 'id',
        ])]
        Favorite $favorite,
    ): RedirectResponse {
        $this->handle(
            new DeleteEntity(
                class: Favorite::class,
                id: $favorite->getId(),
            ),
        );

        $this->addFlash(
            type: 'success',
            message: 'favorite.flashes.deleted',
        );

        return $this->redirectToRoute(
            route: RouteCollection::LIST->prefixed(),
        );
    }
}
