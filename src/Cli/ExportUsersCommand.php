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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Mime\Address;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:users:export')]
class ExportUsersCommand extends Command
{
    use HandleTrait;

    public function __construct(
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

        $user = $this->userRepository->find(1);
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
}
