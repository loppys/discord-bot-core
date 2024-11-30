<?php

namespace Discord\Bot\System\Traits;

trait SingletonTrait
{
    private static mixed $instance = null;

    public static function getInstance(): static
    {
        return static::$instance;
    }
}