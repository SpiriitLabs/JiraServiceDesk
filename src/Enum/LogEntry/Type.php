<?php

namespace App\Enum\LogEntry;

use App\Enum\Contracts\LabeledValueInterface;
use App\Enum\Trait\ValueCasesTrait;

enum Type: string implements LabeledValueInterface
{
    use ValueCasesTrait;

    case EMAIL = 'email';

    public function label(): string
    {
        return sprintf('log.type.%s.label', mb_strtolower($this->name));
    }
}
