<?php

declare(strict_types=1);

namespace App\Controller\Admin\Favorite;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AdminRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AdminRouteCollectionTrait;

    case LIST = 'favorite_list';
    case DELETE = 'favorite_delete';
}
