<?php

namespace App\Controller\App;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IntroductionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(
        path: '/api/introduction/{id}/check',
        name: RouteCollection::API_INTRODUCTION_CHECK->value,
        methods: [Request::METHOD_POST],
    )]
    public function apiCheck(
        #[MapEntity(mapping: [
            'id' => 'id',
        ])]
        User $user,
    ): Response {
        $user->hasCompletedIntroduction = true;
        $this->entityManager->flush();

        return new Response(status: 200);
    }

    #[Route(
        path: '/introduction/{id}/restart',
        name: RouteCollection::INTRODUCTION_RESTART->value,
        methods: [Request::METHOD_GET],
    )]
    public function restart(
        #[MapEntity(mapping: [
            'id' => 'id',
        ])]
        User $user,
    ): RedirectResponse {
        $user->hasCompletedIntroduction = false;
        $this->entityManager->flush();

        return $this->redirectToRoute(RouteCollection::DASHBOARD->prefixed());
    }
}
