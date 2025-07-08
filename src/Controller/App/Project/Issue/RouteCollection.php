<?php

namespace App\Controller\App\Project\Issue;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AppRouteCollectionTrait;

    case CREATE = 'project_issue_create';
    case LIST = 'project_issue_list';
    case BACKLOG_LIST = 'project_issues_backlog_list';
    case VIEW = 'project_issue_view';
    case EDIT = 'project_issue_edit';
    case VIEW_ATTACHMENTS_STREAM = 'project_issue_attachments_stream';
    case SHOW_USER_LIST = 'project_issue_show_user_list';
    case SHOW_USER_LIST_NEXT = 'project_issue_show_user_list_next';
    case TRANSITION_TO = 'project_issue_transition_to';
    case API_TRANSITION_TO = 'project_issue_api_transition_to';
}
