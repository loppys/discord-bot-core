<?php

namespace Discord\Bot\System\Interfaces;

interface SingletonInterface
{
    public static function getInstance(): static;
}
