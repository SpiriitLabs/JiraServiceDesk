<?php

namespace App\Controller\App\Project\Issue;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AppRouteCollectionTrait;

    case CREATE = 'project_issue_create';
    case LIST = 'project_issue_list';
    case VIEW = 'project_issue_view';
    case VIEW_ATTACHMENTS_STREAM = 'project_issue_attachments_stream';
    case SHOW_BACKLOG_LIST = 'project_issue_show_backlog_list';
    case SHOW_BACKLOG_LIST_NEXT = 'project_issue_show_backlog_list_next';
}
