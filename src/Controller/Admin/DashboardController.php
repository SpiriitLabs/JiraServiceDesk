<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\PriorityRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
                'users_count_preference_notification_issue_created' => $this->countUserByJsonPreference(
                    'preferenceNotificationIssueCreated'
                ),
                'users_count_preference_notification_issue_updated' => $this->countUserByJsonPreference(
                    'preferenceNotificationIssueUpdated'
                ),
                'users_count_preference_notification_comment_created' => $this->countUserByJsonPreference(
                    'preferenceNotificationCommentCreated'
                ),
                'users_count_preference_notification_comment_updated' => $this->countUserByJsonPreference(
                    'preferenceNotificationCommentUpdated'
                ),
            ],
        );
    }

    private function countUserByJsonPreference(string $preferenceProperty): int
    {
        return (int) $this->userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.' . $preferenceProperty . ' != :empty')
            ->setParameter('empty', '[]')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
