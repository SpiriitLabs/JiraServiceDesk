<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Controller\Common\EditControllerTrait;
use App\Entity\User;
use App\Exception\User\CurrentPasswordWrongException;
use App\Exception\User\PasswordAlreadyUseException;
use App\Form\Admin\User\UserChangePasswordFormType;
use App\Message\Command\User\ChangePasswordUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/change-password',
    name: RouteCollection::CHANGE_PASSWORD->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class ChangePasswordController extends AbstractController
{
    use EditControllerTrait;

    public function __invoke(
        Request $request,
        #[CurrentUser]
        User $user,
    ): Response {
        $form = $this->createForm(UserChangePasswordFormType::class, new ChangePasswordUser($user));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handle($form->getData());
            } catch (HandlerFailedException $exception) {
                if (
                    $exception->getPrevious() instanceof CurrentPasswordWrongException
                    || $exception->getPrevious() instanceof PasswordAlreadyUseException
                ) {
                    $this->addFlash(
                        type: 'danger',
                        message: $exception->getPrevious()
                            ->getMessage(),
                    );

                    return $this->redirectToRoute(RouteCollection::CHANGE_PASSWORD->prefixed());
                }
                throw $exception;
            }

            $this->addFlash(
                type: 'success',
                message: 'flash.edited',
            );

            return $this->redirectToRoute(route: RouteCollection::REDIRECT_AFTER_LOGIN->prefixed());
        }

        return $this->render(
            view: 'app/user/change-password.html.twig',
            parameters: [
                'form' => $form->createView(),
            ],
        );
    }
}
