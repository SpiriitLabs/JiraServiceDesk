<?php

declare(strict_types=1);

namespace App\Controller\Common;

use Symfony\Component\HttpFoundation\Request;

trait GetRefererRequestTrait
{
    public function getRefererLink(Request $request): ?string
    {
        return $request->headers->get('referer');
    }
}
