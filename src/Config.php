<?php

namespace Discord\Bot;

class Config
{
    protected static array $databaseParams = [
        'dbType' => 'pdo_mysql',
        'dbHost' => 'localhost',
        'dbName' => 'discord_bot_new',
        'dbLogin' => 'root',
        'dbPassword' => ''
    ];

    protected static string $symbolCommand = '~';

    protected static bool $useNewCommandSystem = true;

    public static function getDatabaseParams(): array
    {
        return self::$databaseParams;
    }

    public static function getSymbolCommand(): string
    {
        return self::$symbolCommand;
    }

    public static function isUseNewCommandSystem(): bool
    {
        return self::$useNewCommandSystem;
    }
}
