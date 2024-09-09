<?php

namespace Discord\Bot\System\Events\Interfaces;

interface EventListenerInterface
{
    public function fireEvent(string $methodName, array $arguments): void;
}