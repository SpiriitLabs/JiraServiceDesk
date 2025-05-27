<?php

namespace App\Controller\App\Favorite;

use App\Controller\Common\CreateControllerTrait;
use App\Entity\User;
use App\Form\App\Favorite\FavoriteFormType;
use App\Message\Command\App\Favorite\CreateFavorite;
use App\Message\Command\App\Favorite\DeleteFavorite;
use App\Repository\FavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\UX\Turbo\TurboBundle;

#[Route(
    path: '/favorite/{code}/stream',
    name: RouteCollection::FAVORITE_STREAM->value,
)]
class FavoriteStreamController extends AbstractController
{
    use CreateControllerTrait;

    public function __construct(
        private readonly FavoriteRepository $favoriteRepository,
    ) {
    }

    public function __invoke(
        Request $request,
        string $code,
        #[CurrentUser]
        User $user,
        #[MapQueryParameter]
        ?string $link = null,
        #[MapQueryParameter]
        ?string $name = null,
    ): Response {
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        $link = urldecode($link);
        $favoriteEntity = $this->favoriteRepository->findOneBy([
            'code' => $code,
            'user' => $user,
        ]);

        $form = $this->createForm(FavoriteFormType::class, [
            'code' => $code,
            'link' => $link,
            'name' => $name,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($favoriteEntity !== null) {
                $this->handle(
                    new DeleteFavorite(
                        $form->get('code')
                            ->getData(),
                        $user,
                    ),
                );

                return $this->renderBlock(
                    view: 'components/app/favorite/favorite.html.twig',
                    block: 'add',
                    parameters: [
                        'form' => $form,
                    ],
                );
            }
            $this->handle(
                new CreateFavorite(
                    $form->get('code')
                        ->getData(),
                    $form->get('name')
                        ->getData(),
                    $form->get('link')
                        ->getData(),
                    $user,
                ),
            );

            return $this->renderBlock(
                view: 'components/app/favorite/favorite.html.twig',
                block: 'remove',
                parameters: [
                    'form' => $form,
                ],
            );
        }

        return $this->renderBlock(
            view: 'components/app/favorite/favorite.html.twig',
            block: $favoriteEntity == null ? 'add' : 'remove',
            parameters: [
                'form' => $form,
            ],
        );
    }
}
