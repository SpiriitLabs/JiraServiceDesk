<?php

namespace App\Model\PushNotification;

enum Actions: string
{
    case OPEN = 'open';
    case CLOSE = 'close';
}
