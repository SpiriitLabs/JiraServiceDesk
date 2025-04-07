<?php

namespace App\Controller\App\Project;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AppRouteCollectionTrait;

    case LIST = 'project_list';
    case VIEW = 'project_view';
    case PROJECT_BOARD_VIEW = 'project_board_view';
}
