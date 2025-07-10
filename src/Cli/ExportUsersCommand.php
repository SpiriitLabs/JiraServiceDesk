<?php

declare(strict_types=1);

namespace App\Cli;

use App\Message\Command\Admin\User\ExportUsers;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:users:export')]
class ExportUsersCommand extends Command
{
    use HandleTrait;

    public function __construct(
        #[Autowire(service: 'serializer.encoder.csv')]
        private readonly CsvEncoder $csvEncoder,
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask(
            "A quel user envoyer l'export ?",
            null,
            Validation::createCallable(new NotBlank(), new Email())
        );

        $users = $this->userRepository->findBy([
            'email' => $email,
        ]);
        $user = reset($users);
        $csv = $this->handle(new ExportUsers(user: $user));

        $emailToSent = (new TemplatedEmail())
            ->htmlTemplate('email/user/export.html.twig')
            ->attach($csv, 'user.csv')
            ->subject(
                $this->translator->trans(
                    id: 'user.export.title',
                    domain: 'email',
                    locale: $user->preferredLocale->value,
                ),
            )
            ->to(new Address($user->email, $user->getFullName()))
            ->locale($user->preferredLocale->value)
        ;

        $this->mailer->send($emailToSent);

        $io->success('Export finished :  users exported"');

        return self::SUCCESS;
    }

    #[Required]
    public function setMessageBus(MessageBusInterface $queryBus): void
    {
        $this->messageBus = $queryBus;
    }
}
