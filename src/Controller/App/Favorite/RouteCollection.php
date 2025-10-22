<?php

declare(strict_types=1);

namespace App\Controller\App\Favorite;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AppRouteCollectionTrait;

    case FAVORITE_STREAM = 'favorite_stream';
}
