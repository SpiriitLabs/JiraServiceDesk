<?php

namespace App\Controller\App\Project\Board;

use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string
{
    use AppRouteCollectionTrait;

    case LIST = 'project_board_stream_list';
}
