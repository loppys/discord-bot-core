<?php

namespace Discord\Bot\System\Events;

use Discord\Bot\System\Events\Interfaces\EventListenerInterface;

abstract class AbstractEventListener implements EventListenerInterface
{
    public function fireEvent(string $methodName, array $arguments = []): void
    {
        if (!method_exists($this, $methodName)) {
            return;
        }

        print PHP_EOL . "call {$methodName}" . PHP_EOL;

        call_user_func_array([$this, $methodName], $arguments);
    }
}