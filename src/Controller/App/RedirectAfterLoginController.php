<?php

namespace App\Controller\App;

use App\Controller\Admin\RouteCollection as AdminRouteCollection;
use App\Entity\User;
use App\Enum\User\Role;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/redirect_after_login',
    name: RouteCollection::REDIRECT_AFTER_LOGIN->value,
    methods: [Request::METHOD_GET]
)]
class RedirectAfterLoginController extends AbstractController
{
    public function __invoke(
        #[CurrentUser]
        User $user,
    ): Response {
        if ($this->isGranted(Role::ROLE_ADMIN)) {
            return $this->redirectToRoute(AdminRouteCollection::DASHBOARD->prefixed());
        }

        if ($user->defaultProject !== null) {
            return $this->redirectToRoute(Project\RouteCollection::VIEW->prefixed(), [
                'key' => $user->defaultProject->jiraKey,
            ]);
        }

        if ($user->getProjects()->count() == 1) {
            return $this->redirectToRoute(Project\RouteCollection::VIEW->prefixed(), [
                'key' => $user->getProjects()
                    ->first()
                    ->jiraKey,
            ]);
        }

        return $this->redirectToRoute(RouteCollection::PROJECT_SELECT->prefixed());
    }
}
