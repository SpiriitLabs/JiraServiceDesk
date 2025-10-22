<?php

declare(strict_types=1);

namespace App\Controller\Admin\Notification;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AdminRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AdminRouteCollectionTrait;

    case LIST = 'notification_list';
    case VIEW = 'notification_view';
}
