<?php

declare(strict_types=1);

namespace App\Enum\Notification;

use App\Enum\Contracts\LabeledValueInterface;
use App\Enum\Trait\ValueCasesTrait;

enum NotificationChannel: string implements LabeledValueInterface
{
    use ValueCasesTrait;

    case IN_APP = 'in_app';
    case EMAIL = 'email';
    case SLACK = 'slack';

    public function label(): string
    {
        return sprintf('notificationChannel.%s', $this->value);
    }
}
