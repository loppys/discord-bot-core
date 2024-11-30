<?php

namespace Discord\Bot\System\Events;

use Discord\Bot\System\Events\Interfaces\EventListenerInterface;
use Discord\Bot\System\Helpers\ConsoleLogger;

class EventDispatcher
{
    /**
     * @var EventListenerInterface[]
     */
    private array $listeners = [];

    public function addListener(string $eventName, EventListenerInterface $listener): void
    {
        ConsoleLogger::showMessage("add event listener: {$eventName}");

        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(string $eventName, string $methodName, array $arguments = []): void
    {
        if (isset($this->listeners[$eventName])) {
            /** @var EventListenerInterface $listener */
            foreach ($this->listeners[$eventName] as $listener) {
                ConsoleLogger::showMessage("event call: {$eventName}");
                $listener->fireEvent($methodName, $arguments);
            }
        }
    }
}
