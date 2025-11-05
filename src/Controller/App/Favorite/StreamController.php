<?php

declare(strict_types=1);

namespace App\Controller\App\Favorite;

use App\Controller\Common\CreateControllerTrait;
use App\Entity\Project;
use App\Entity\User;
use App\Form\App\Favorite\FavoriteFormType;
use App\Message\Command\App\Favorite\CreateFavorite;
use App\Message\Command\App\Favorite\DeleteFavorite;
use App\Repository\FavoriteRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\UX\Turbo\TurboBundle;

#[Route(
    path: '/favorite/{projectId}/{code}/stream',
    name: RouteCollection::FAVORITE_STREAM->value,
)]
class StreamController extends AbstractController
{
    use CreateControllerTrait;

    public function __construct(
        private readonly FavoriteRepository $favoriteRepository,
    ) {
    }

    public function __invoke(
        Request $request,
        string $code,
        #[MapEntity(mapping: [
            'projectId' => 'id',
        ])]
        Project $project,
        #[CurrentUser]
        User $user,
        #[MapQueryParameter]
        ?string $link = null,
        #[MapQueryParameter]
        ?string $name = null,
    ): Response {
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        $link = $request->request->all()['favorite_form']['link'] ?? $link;
        $link = urldecode($link);
        $name = $request->request->all()['favorite_form']['name'] ?? $name;
        $favoriteEntity = $this->favoriteRepository->findOneBy([
            'code' => $code,
            'user' => $user,
            'project' => $project,
        ]);

        $form = $this->createForm(FavoriteFormType::class, [
            'code' => $code,
            'projectId' => $project->getId(),
            'link' => $link,
            'name' => $name,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($favoriteEntity !== null) {
                $this->handle(
                    new DeleteFavorite(
                        code: $form->get('code')
                            ->getData(),
                        projectId: (int) $form->get('projectId')
                            ->getData(),
                        user: $user,
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
                    code: $form->get('code')
                        ->getData(),
                    projectId: (int) $form->get('projectId')
                        ->getData(),
                    user: $user,
                    name: $form->get('name')
                        ->getData(),
                    link: $form->get('link')
                        ->getData(),
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
