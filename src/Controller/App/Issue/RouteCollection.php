<?php

namespace App\Controller\App\Issue;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AppRouteCollectionTrait;

    case VIEW = 'issue_view';
    case TRANSITION_TO = 'issue_transition_to';
}
