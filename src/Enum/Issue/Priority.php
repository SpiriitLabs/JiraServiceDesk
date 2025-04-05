<?php

namespace App\Enum\Issue;

use App\Enum\Contracts\LabeledValueInterface;
use App\Enum\Trait\ValueCasesTrait;

enum Priority: string implements LabeledValueInterface
{
    use ValueCasesTrait;

    case LOWEST = 'Lowest';
    case LOW = 'Low';
    case NORMAL = 'Medium';
    case HIGH = 'High';
    case HIGHEST = 'Highest';

    public function label(): string
    {
        return sprintf(
            'issue.priority.%s',
            strtolower($this->value),
        );
    }
}
