<?php

namespace Discord\Bot\System\Events;

use ReflectionException;
use Loader\System\Container;
use Discord\Bot\System\Events\Interfaces\EventListenerInterface;

abstract class AbstractSystemEventHandle
{
    protected EventDispatcher $eventDispatcher;

    /**
     * @var EventListenerInterface[]|string[]
     */
    protected array $_events = [];

    /**
     * @throws ReflectionException
     */
    public function __construct()
    {
        /** @var EventDispatcher $eventDispatcher */
        $this->eventDispatcher = Container::getInstance()->getShared('eventDispatcher');

        foreach ($this->_events as $listener => $item) {
            if (is_string($listener)) {
                if (class_exists($listener)) {
                    $listener = Container::getInstance()->createObject($listener);
                } else {
                    continue;
                }
            }

            if (is_array($item)) {
                foreach ($item as $event) {
                    $this->eventDispatcher->addListener($event, $listener);
                }
            }

            if (is_string($item)) {
                $this->eventDispatcher->addListener($item, $listener);
            }
        }
    }

    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this, "{$name}Eventable")) {
            trigger_error('calling a non-event method or non-public method', E_USER_WARNING);

            return;
        }

        $method = lcfirst($name);

        $this->eventDispatcher->dispatch("before.{$name}", "before{$method}", $arguments);

        $result = call_user_func_array([$this, "{$name}Eventable"], $arguments);

        $this->eventDispatcher->dispatch("after.{$name}", "after{$method}", $arguments);

        return $result;
    }
}
