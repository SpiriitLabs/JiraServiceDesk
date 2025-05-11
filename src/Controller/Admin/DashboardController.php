<?php

namespace App\Controller\Admin;

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
        private readonly UserRepository $userRepository,
    ){}

    public function __invoke(): Response
    {
        return $this->render(
            view: 'admin/dashboard.html.twig',
            parameters: [
                'projects_count' => $this->projectRepository->count(),
                'users_count' => $this->userRepository->count(),
                'user_preference_notification' => $this->userRepository->createQueryBuilder('u')->select('COUNT(u.id)')->where('u.preferenceNotification = :active')->setParameter('active', true)->getQuery()->getSingleScalarResult(),
            ],
        );
    }
}
