<?php

namespace App\Controller\App\Favorite;

use App\Controller\Common\CreateControllerTrait;
use App\Entity\User;
use App\Message\Command\App\Favorite\CreateFavorite;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/api/favorite/add',
    name: RouteCollection::API_ADD_FAVORITE->value,
    methods: [Request::METHOD_POST, Request::METHOD_GET],
)]
class ApiAddController extends AbstractController
{
    use CreateControllerTrait;

    public function __invoke(
        Request $request,
        #[CurrentUser]
        User $user,
    ): Response {
        $favoriteCode = $request->request->get('code');
        $favoriteName = $request->request->get('name');
        $favoriteLink = $request->request->get('link');

        if ($favoriteCode === null || $favoriteName === null || $favoriteLink === null) {
            throw new BadRequestHttpException();
        }

        $favorite = $this->handle(
            new CreateFavorite(
                code: $favoriteCode,
                name: $favoriteName,
                link: $favoriteLink,
                user: $user,
            ),
        );

        if ($favorite === null) {
            throw new BadRequestHttpException();
        }

        $this->addFlash(
            type: 'success',
            message: 'flash.success',
        );

        return $this->render(
            view: 'components/app/favorite/remove_from_favorite.html.twig',
            parameters: [
                'code' => $favoriteCode,
                'name' => $favoriteName,
                'link' => $favoriteLink,
            ],
        );
    }
}
