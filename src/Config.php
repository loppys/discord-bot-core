<?php

namespace Discord\Bot;

class Config
{
    protected static string $symbolCommand = '~';

    protected static bool $useNewCommandSystem = true;

    public static function setSymbolCommand(string $symbolCommand): void
    {
        self::$symbolCommand = $symbolCommand;
    }

    public static function setUseNewCommandSystem(bool $useNewCommandSystem): void
    {
        self::$useNewCommandSystem = $useNewCommandSystem;
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
