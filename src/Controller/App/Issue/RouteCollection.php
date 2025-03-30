<?php

namespace App\Controller\App\Issue;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AppRouteCollectionTrait;
    case LIST = 'issue_list';
    case VIEW = 'issue_view';
}
