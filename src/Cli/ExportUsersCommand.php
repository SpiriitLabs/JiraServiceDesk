<?php

declare(strict_types=1);

namespace App\Cli;

use App\Message\Command\Admin\User\ExportUsers;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:users:export')]
class ExportUsersCommand extends Command
{
    use HandleTrait;

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('emails', InputArgument::REQUIRED, 'emails to send, use , to separate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emails = $input->getArgument('emails');
        $emails = explode(',', $emails);

        $csv = $this->handle(new ExportUsers());

        $emailToSent = (new TemplatedEmail())
            ->htmlTemplate('email/user/export.html.twig')
            ->attach($csv, 'user.csv')
            ->subject(
                $this->translator->trans(
                    id: 'user.export.title',
                    domain: 'email',
                    locale: 'fr',
                ),
            )
            ->locale('fr')
        ;

        foreach ($emails as $email) {
            $emailToSent->addTo(new Address($email));
        }

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
