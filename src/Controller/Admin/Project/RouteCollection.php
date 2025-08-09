<?php

namespace App\Controller\Admin\Project;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AdminRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AdminRouteCollectionTrait;

    case LIST = 'project_list';
    case EDIT = 'project_edit';
    case CREATE = 'project_create';
    case DELETE = 'project_delete';
    case EXPORT = 'project_export';
}
