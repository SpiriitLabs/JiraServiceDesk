<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AdminRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AdminRouteCollectionTrait;

    case LIST = 'user_list';
    case EDIT = 'user_edit';
    case CREATE = 'user_create';
    case DELETE = 'user_delete';
    case EXPORT = 'user_export';
}
