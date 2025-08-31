<?php

namespace App\Enum\LogEntry;

enum LogType: string
{
    case EMAIL = 'email';

    /**
     * @return array<string,string>
     */
    public static function getSearchList(): array
    {
        return [
            'Tout' => null,
            self::EMAIL->value => self::EMAIL->value,
        ];
    }
}
