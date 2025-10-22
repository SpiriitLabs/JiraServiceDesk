<?php

declare(strict_types=1);

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
        return sprintf('logs.level.%s.label', mb_strtolower($this->name));
    }

    public function color(): string
    {
        return match ($this) {
            self::DEBUG => 'secondary',
            self::INFO => 'primary',
        };
    }
}
