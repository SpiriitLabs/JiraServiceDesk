<?php

namespace App\Controller\Admin\Priority;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AdminRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AdminRouteCollectionTrait;

    case LIST = 'priority_list';
    case GENERATE = 'priority_generate';
}
