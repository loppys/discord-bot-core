<?php

namespace Discord\Bot;

class Config
{
    protected static array $databaseParams = [
        'dbType' => '',
        'dbHost' => '',
        'dbName' => '',
        'dbLogin' => '',
        'dbPassword' => ''
    ];

    protected static string $symbolCommand = '~';

    protected static bool $useNewCommandSystem = true;

    public static function setDatabaseParams(array $databaseParams): void
    {
        self::$databaseParams = $databaseParams;
    }

    public static function setSymbolCommand(string $symbolCommand): void
    {
        self::$symbolCommand = $symbolCommand;
    }

    public static function setUseNewCommandSystem(bool $useNewCommandSystem): void
    {
        self::$useNewCommandSystem = $useNewCommandSystem;
    }

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
