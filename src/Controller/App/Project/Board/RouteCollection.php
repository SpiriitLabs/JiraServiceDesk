<?php

declare(strict_types=1);

namespace App\Controller\App\Project\Board;

use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string
{
    use AppRouteCollectionTrait;

    case LIST = 'project_board_list_stream';
    case VIEW = 'project_board_view';
    case VIEW_STREAM = 'project_board_view_stream';
}
