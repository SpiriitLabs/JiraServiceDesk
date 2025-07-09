<?php

namespace App\Message\Command\Admin\User\Handler;

use App\Message\Command\Admin\User\ExportUsers;
use App\Message\Command\Common\EmailNotification;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class ExportUsersHandler
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private UserRepository $userRepository,
        #[Autowire(service: 'serializer.encoder.csv')]
        private CsvEncoder $csvEncoder,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(ExportUsers $command): void
    {
        $users = $this->userRepository->findAll();

        $content = [];

        foreach ($users as $userToExport) {
            $projects = array_map(function ($project) {
                return $project->jiraKey;
            }, $userToExport->getProjects()
                ->toArray());

            $content[] = [
                'email' => $userToExport->email,
                'nom' => $userToExport->getLastName(),
                'prénom' => $userToExport->firstName,
                'société' => $userToExport->company,
                'projets' => implode(', ', $projects),
            ];
        }

        $csv = $this->csvEncoder->encode($content, 'csv');

        $templatedEmail = (new TemplatedEmail())
            ->htmlTemplate('email/user/export.html.twig')
            ->attach($csv, 'user.csv')
        ;

        $emailToSent = clone $templatedEmail
            ->subject(
                $this->translator->trans(
                    id: 'user.export.title',
                    domain: 'email',
                    locale: $command->user->preferredLocale->value,
                ),
            )
            ->to(new Address($command->user->email, $command->user->getFullName()))
            ->locale($command->user->preferredLocale->value)
        ;

        $this->commandBus->dispatch(
            new EmailNotification(
                user: $command->user,
                email: $emailToSent,
            ),
        );
    }
}
