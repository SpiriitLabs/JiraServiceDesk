<?php

namespace App\Enum\LogEntry;

use App\Enum\Contracts\LabeledValueInterface;
use App\Enum\Trait\ValueCasesTrait;

enum LogType: string implements LabeledValueInterface
{
    use ValueCasesTrait;
    case EMAIL = 'email';

    public function label(): string
    {
        return sprintf('logType.%s', mb_strtolower($this->name));
    }
}
