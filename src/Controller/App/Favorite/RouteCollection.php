<?php

namespace App\Controller\App\Favorite;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AppRouteCollectionTrait;

    case API_ADD_FAVORITE = 'api_add_favorite';
}
