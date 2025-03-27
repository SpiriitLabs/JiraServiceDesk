<?php

namespace App\Controller\App\Project;

enum RouteCollection: string
{
    case LIST = 'project_list';
    case VIEW = 'project_view';
    case PROJECT_BOARD_VIEW = 'project_board_view';
}
