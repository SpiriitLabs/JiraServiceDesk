<?php

namespace App\Factory;

use App\Entity\LogEntry;
use App\Enum\LogEntry\Level;
use App\Enum\LogEntry\Type;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<LogEntry>
 */
final class LogEntryFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return LogEntry::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'type' => self::faker()->randomElement(Type::cases()),
            'level' => self::faker()->randomElement(Level::cases()),
            'subject' => self::faker()->text(),
            'datas' => [
                'key' => self::faker()->text(20),
                'another_key' => self::faker()->text(20),
                'integer' => self::faker()->randomNumber(),
                'float' => self::faker()->randomFloat(2, 0, 1000),
                'boolean' => self::faker()->boolean(),
            ],
            'logAt' => self::faker()->dateTime(),
        ];
    }
}
