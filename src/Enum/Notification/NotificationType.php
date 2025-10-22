<?php

declare(strict_types=1);

namespace App\Enum\Notification;

use App\Enum\Contracts\LabeledValueInterface;
use App\Enum\Trait\ValueCasesTrait;

enum NotificationType: string implements LabeledValueInterface
{
    use ValueCasesTrait;

    case ISSUE_CREATED = 'issue.created';
    case ISSUE_UPDATED = 'issue.updated';
    case ISSUE_DELETED = 'issue.deleted';
    case COMMENT_CREATED = 'comment.created';
    case COMMENT_UPDATED = 'comment.updated';

    public function label(): string
    {
        return sprintf('notificationType.%s', mb_strtolower($this->value));
    }
}
