<?php

namespace App\Enum\LogEntry;

use App\Enum\Contracts\LabeledValueInterface;
use App\Enum\Trait\ValueCasesTrait;

enum Level: string implements LabeledValueInterface
{
    use ValueCasesTrait;
    case DEBUG = 'debug';
    case INFO = 'info';

    public function label(): string
    {
        return sprintf('log.level.%s.label', mb_strtolower($this->name));
    }
}
