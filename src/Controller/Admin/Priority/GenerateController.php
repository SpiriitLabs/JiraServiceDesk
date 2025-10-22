<?php

declare(strict_types=1);

namespace App\Controller\Admin\Priority;

use App\Controller\Common\CreateControllerTrait;
use App\Message\Command\Admin\Priority\GeneratePriorities;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/priority/generate',
    name: RouteCollection::GENERATE->value,
    methods: [Request::METHOD_POST],
)]
class GenerateController extends AbstractController
{
    use CreateControllerTrait;

    public function __invoke(): RedirectResponse
    {
        $this->handle(new GeneratePriorities());

        $this->addFlash(
            type: 'success',
            message: 'priority.flashes.generated',
        );

        return $this->redirectToRoute(RouteCollection::LIST->prefixed());
    }
}
