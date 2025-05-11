<?php

namespace App\Controller\Admin;

use App\Repository\PriorityRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(
    path: '/dashboard',
    name: RouteCollection::DASHBOARD->value,
    methods: [Request::METHOD_GET],
)]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly PriorityRepository $priorityRepository,
        private readonly UserRepository $userRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->render(
            view: 'admin/dashboard.html.twig',
            parameters: [
                'projects_count' => $this->projectRepository->count(),
                'priorities_count' => $this->priorityRepository->count(),
                'users_count' => $this->userRepository->count(),
                'users_count_preference_notification' => $this->countUserByPreference('preferenceNotification'),
                'users_count_preference_notification_issue_created' => $this->countUserByPreference(
                    'preferenceNotificationIssueCreated'
                ),
                'users_count_preference_notification_issue_updated' => $this->countUserByPreference(
                    'preferenceNotificationIssueUpdated'
                ),
                'users_count_preference_notification_comment_created' => $this->countUserByPreference(
                    'preferenceNotificationCommentCreated'
                ),
                'users_count_preference_notification_comment_updated' => $this->countUserByPreference(
                    'preferenceNotificationCommentUpdated'
                ),
            ],
        );
    }

    private function countUserByPreference(string $preferenceProperty = 'preferenceNotification'): int
    {
        return $this->userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.' . $preferenceProperty . ' = :active')->setParameter(
                'active',
                true
            )->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
