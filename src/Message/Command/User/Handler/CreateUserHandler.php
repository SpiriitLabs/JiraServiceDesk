<?php

declare(strict_types=1);

namespace App\Message\Command\User\Handler;

use App\Entity\User;
use App\Enum\User\Locale;
use App\Message\Command\User\CreateUser;
use App\Message\Trait\UserHandlerTrait;
use App\Subscriber\Event\NotificationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[AsMessageHandler]
readonly class CreateUserHandler
{
    use UserHandlerTrait;

    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private TranslatorInterface $translator,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer,
    ) {
    }

    public function __invoke(CreateUser $command): User
    {
        $user = new User(
            email: $command->email,
            firstName: $command->firstName,
            lastName: $command->lastName,
            company: $command->company,
            enabled: $command->enabled,
        );
        $this->updateUserFields($user, $command);

        if ($command->plainPassword !== null) {
            $password = $this->passwordHasher->hashPassword($user, $command->plainPassword);
            $user->setPassword($password);
        }

        foreach ($command->projects as $project) {
            $user->addProject($project);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $resetUserToken = $this->resetPasswordHelper->generateResetToken($user);

        $subject = $this->translator->trans(
            id: 'security.create_account.title',
            domain: 'email',
            locale: $user->preferredLocale->value,
        );
        $email = (new TemplatedEmail())
            ->to(new Address($user->email, $user->getFullName()))
            ->locale($user->preferredLocale->value ?? Locale::FR->value)
            ->subject($subject)
            ->htmlTemplate('email/security/create_account.html.twig')
            ->context([
                'resetToken' => $resetUserToken,
                'createdUser' => $user,
            ])
        ;

        $this->mailer->send($email);

        $this->dispatcher->dispatch(
            new NotificationEvent(
                user: $user,
                message: sprintf('Create account email sent to "%s"', $user->email),
                extraData: [
                    'subject' => $subject,
                ],
            ),
            NotificationEvent::EVENT_NAME,
        );

        return $user;
    }
}
