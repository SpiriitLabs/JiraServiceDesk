<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Controller\Common\EditControllerTrait;
use App\Entity\User;
use App\Form\Admin\User\UserProfileFormType;
use App\Message\Command\User\EditUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/profile',
    name: RouteCollection::PROFIL->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class ProfilController extends AbstractController
{
    use EditControllerTrait;

    public function __invoke(
        Request $request,
        #[CurrentUser]
        User $user,
    ): Response {
        $form = $this->createForm(UserProfileFormType::class, new EditUser($user));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handle($form->getData());

            $this->addFlash(
                type: 'success',
                message: 'flash.edited',
            );

            return $this->redirectToRoute(route: RouteCollection::PROFIL->prefixed());
        }

        return $this->render(
            view: 'app/user/profil.html.twig',
            parameters: [
                'form' => $form->createView(),
            ],
        );
    }
}
