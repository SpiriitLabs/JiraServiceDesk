<?php

namespace App\Controller\Admin\LogEntry;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AdminRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AdminRouteCollectionTrait;

    case LIST = 'log_entry_list';
}
