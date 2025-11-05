<?php

declare(strict_types=1);

namespace App\Controller\Admin\IssueLabel;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AdminRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AdminRouteCollectionTrait;

    case LIST = 'issue_label_list';
}
