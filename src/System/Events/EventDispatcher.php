<?php

namespace Discord\Bot\System\Events;

use Discord\Bot\System\Events\Interfaces\EventListenerInterface;

class EventDispatcher
{
    /**
     * @var EventListenerInterface[]
     */
    private array $listeners = [];

    public function addListener(string $eventName, EventListenerInterface $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(string $eventName, string $methodName, array $arguments = []): void
    {
        if (isset($this->listeners[$eventName])) {
            /** @var EventListenerInterface $listener */
            foreach ($this->listeners[$eventName] as $listener) {
                $listener->fireEvent($methodName, $arguments);
            }
        }
    }
}
