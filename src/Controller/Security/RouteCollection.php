<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\SecurityRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use SecurityRouteCollectionTrait;

    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case FORGOT_PASSWORD_REQUEST = 'forgot_password_request';
    case FORGOT_PASSWORD_CHECK_EMAIL = 'forgot_password_check_email';
    case FORGOT_PASSWORD_RESET = 'forgot_password_reset';
}
