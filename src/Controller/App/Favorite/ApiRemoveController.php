<?php

namespace App\Controller\App\Favorite;

use App\Controller\Common\DeleteControllerTrait;
use App\Entity\User;
use App\Message\Command\App\Favorite\DeleteFavorite;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/api/favorite/remove',
    name: RouteCollection::API_REMOVE_FAVORITE->value,
    methods: [Request::METHOD_POST],
)]
class ApiRemoveController extends AbstractController
{
    use DeleteControllerTrait;

    public function __invoke(
        Request $request,
        #[CurrentUser]
        User $user,
    ): Response {
        $favoriteCode = $request->request->get('code');
        $favoriteName = $request->request->get('name');
        $favoriteLink = $request->request->get('link');

        $this->handle(
            new DeleteFavorite(
                $favoriteCode,
                $user,
            ),
        );

        return $this->render(
            view: 'components/app/favorite/add_to_favorite.html.twig',
            parameters: [
                'code' => $favoriteCode,
                'name' => $favoriteName,
                'link' => $favoriteLink,
            ],
        );
    }
}
