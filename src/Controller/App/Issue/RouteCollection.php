<?php

namespace App\Controller\App\Issue;

enum RouteCollection: string
{
    case LIST = 'issue_list';
    case VIEW = 'issue_view';
}
