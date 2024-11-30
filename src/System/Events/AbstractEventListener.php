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

        call_user_func_array([$this, $methodName], $arguments);
    }
}