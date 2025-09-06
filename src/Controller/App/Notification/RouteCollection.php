<?php

namespace App\Controller\App\Notification;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AppRouteCollectionTrait;

    case NOTIFICATION_STREAM = 'notification_stream';
}
