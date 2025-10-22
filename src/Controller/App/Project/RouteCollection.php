<?php

declare(strict_types=1);

namespace App\Controller\App\Project;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AppRouteCollectionTrait;

    case VIEW = 'project_view';
    case VIEW_STREAM = 'project_view_stream';
}
