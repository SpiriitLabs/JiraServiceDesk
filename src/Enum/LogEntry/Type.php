<?php

namespace App\Enum\LogEntry;

use App\Enum\Contracts\LabeledValueInterface;
use App\Enum\Trait\ValueCasesTrait;

enum Type: string implements LabeledValueInterface
{
    use ValueCasesTrait;

    case EMAIL = 'email';

    case LOGIN = 'login';

    public function label(): string
    {
        return sprintf('logs.type.%s.label', mb_strtolower($this->name));
    }

    public function icon(): string
    {
        return match ($this) {
            self::EMAIL => 'mdi-email-outline',
            self::LOGIN => 'mdi-login',
        };
    }
}
