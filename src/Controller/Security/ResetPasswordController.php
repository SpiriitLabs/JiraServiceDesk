<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Enum\User\Locale as AppLocale;
use App\Form\Security\ChangePasswordFormType;
use App\Form\Security\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route(
    path: '/security/reset-password',
)]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
        private readonly TranslatorInterface $translator,
        private readonly MailerInterface $mailer,
    ) {
    }

    #[Route(
        '/',
        name: RouteCollection::FORGOT_PASSWORD_REQUEST->value,
    )]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')
                ->getData()
            ;

            return $this->processSendingPasswordResetEmail(
                emailFormData: $email,
            );
        }

        return $this->render(
            view: 'security/reset_password_request.html.twig',
            parameters: [
                'requestForm' => $form,
            ],
        );
    }

    #[Route(
        path: '/check-email',
        name: RouteCollection::FORGOT_PASSWORD_CHECK_EMAIL->value,
    )]
    public function checkEmail(): Response
    {
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render(
            view: 'security/check_email.html.twig',
            parameters: [
                'resetToken' => $resetToken,
            ]
        );
    }

    #[Route(
        path: '/reset/{token}',
        name: RouteCollection::FORGOT_PASSWORD_RESET->value,
    )]
    public function reset(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        TranslatorInterface $translator,
        ?string $token = null
    ): Response {
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute(RouteCollection::FORGOT_PASSWORD_RESET->prefixed());
        }

        $token = $this->getTokenFromSession();
        if ($token === null) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash(
                type: 'warning',
                message: sprintf(
                    '%s - %s',
                    $translator->trans(
                        ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE,
                        [],
                        'ResetPasswordBundle'
                    ),
                    $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
                )
            );

            return $this->redirectToRoute(RouteCollection::FORGOT_PASSWORD_REQUEST->prefixed());
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordHelper->removeResetRequest($token);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')
                ->getData()
            ;

            $passwordHashed = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($passwordHashed);
            $this->entityManager->flush();

            $this->cleanSessionAfterReset();

            return $this->redirectToRoute(RouteCollection::LOGIN->prefixed());
        }

        return $this->render(
            view: 'security/reset_password.html.twig',
            parameters: [
                'resetForm' => $form,
            ]
        );
    }

    private function processSendingPasswordResetEmail(string $emailFormData): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        if ($user === null) {
            return $this->redirectToRoute(RouteCollection::FORGOT_PASSWORD_CHECK_EMAIL->prefixed());
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
            //     $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            // ));

            return $this->redirectToRoute(RouteCollection::FORGOT_PASSWORD_CHECK_EMAIL->prefixed());
        }

        $email = (new TemplatedEmail())
            ->to(new Address($user->email, $user->fullName))
            ->locale($user->preferredLocale->value ?? AppLocale::FR->value)
            ->subject(
                $this->translator->trans(
                    id: 'security.reset_password.title',
                    domain: 'email',
                ),
            )
            ->htmlTemplate('email/security/reset_password.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $this->mailer->send($email);
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute(RouteCollection::FORGOT_PASSWORD_CHECK_EMAIL->prefixed());
    }
}
