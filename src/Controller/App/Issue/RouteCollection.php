<?php

namespace App\Controller\App\Issue;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AppRouteCollectionTrait;

    case LIST = 'issue_list';
    case VIEW = 'issue_view';
    case VIEW_ATTACHMENTS_STREAM = 'issue_view_attachments_stream';
    case PROJECT_VIEW = 'issue_project_view';
    case CREATE = 'issue_create';
    case EDIT = 'issue_edit';
    case TRANSITION_TO = 'issue_transition_to';
    case API_TRANSITION_TO = 'api_issue_transition_to';
}
