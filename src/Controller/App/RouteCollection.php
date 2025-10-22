<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Controller\Contracts\RouteCollectionInterface;
use App\Controller\Traits\AppRouteCollectionTrait;

enum RouteCollection: string implements RouteCollectionInterface
{
    use AppRouteCollectionTrait;

    case LOCALE_FR = 'locale_fr';
    case LOCALE_EN = 'locale_en';
    case THEME_DARK = 'theme_dark';
    case THEME_LIGHT = 'theme_light';
    case REDIRECT_AFTER_LOGIN = 'redirect_after_login';
    case PROJECT_SELECT = 'project_select';
    case ATTACHMENT_PREVIEW = 'attachment_preview';
    case ATTACHMENT = 'attachment';
    case SEARCH_API = 'search_api';

    case PROFIL = 'profil';
}
