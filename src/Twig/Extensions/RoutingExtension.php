<?php

declare(strict_types=1);

namespace App\Twig\Extensions;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RoutingExtension extends AbstractExtension
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_path', fn () => $this->getRoutePathFromRequest()),
            new TwigFunction('current_route', fn () => $this->getRouteFromRequest()),
            new TwigFunction('referer_url', fn () => $this->getRefererUrl()),
        ];
    }

    private function getRouteFromRequest(): string
    {
        $request = $this->requestStack->getMainRequest();

        return $request?->attributes->get('_route') ?? 'app_dashboard';
    }

    private function getRoutePathFromRequest(): string
    {
        $request = $this->requestStack->getMainRequest();

        $currentRoute = $this->getRouteFromRequest();
        /** @psalm-var array $currentRouteParams */
        $currentRouteParams = $request?->attributes->get('_route_params') ?? [];

        return $this->urlGenerator->generate($currentRoute, $currentRouteParams);
    }

    private function getRefererUrl(): string
    {
        $request = $this->requestStack->getMainRequest();

        return $request?->headers?->get('referer') ?? 'app_dashboard';
    }
}
