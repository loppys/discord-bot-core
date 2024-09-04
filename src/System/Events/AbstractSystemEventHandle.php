<?php

namespace Discord\Bot\System\Events;

use Discord\Bot\System\EventHandler;
use Loader\System\Container;
use ReflectionException;

/**
 * @deprecated
 * @see Костыль и временное решение, позже будет переведено на атрибуты. Для автоматического выполнения ивентов необходимо убрать метод из публичного доступа..
 */
abstract class AbstractSystemEventHandle
{
    /**
     * @var callable[]|array[]
     */
    protected array $events = [];

    /**
     * @throws ReflectionException
     */
    public function __construct()
    {
        /** @var EventHandler $eventHandler */
        $eventHandler = Container::getInstance()->getShared('eventHandler');

        foreach ($this->events as $name => $event) {
            if (is_array($event)) {
                foreach ($event as $n => $v) {
                    if (!is_callable($v) && is_array($v)) {
                        $v = $this->_createCallback($v);
                    }

                    if (empty($v)) {
                        continue;
                    }

                    $eventHandler->registerEvent($n, $v);
                }
            } else {
                if (!is_callable($event) && is_array($event)) {
                    $event = $this->_createCallback($event);
                }

                if (empty($event)) {
                    continue;
                }

                $eventHandler->registerEvent($name, $event);
            }
        }
    }

    public function __call(string $name, array $arguments)
    {
        /** @var EventHandler $eventHandler */
        $eventHandler = Container::getInstance()->getShared('eventHandler');

        if (!empty($this->events['before'])) {
            if (is_array($this->events['before'])) {
                foreach ($this->events['before'] as $before) {
                    $eventHandler->fireEvent($before);
                }
            } else {
                $eventHandler->fireEvent($this->events['before']);
            }
        }

        if (!empty($this->events)) {
            foreach ($this->events as $group => $event) {
                if (in_array($group, ['before', 'after'], true)) {
                    continue;
                }

                $eventHandler->fireEvent($event);
            }
        }

        $res = call_user_func_array([$this, $name], $arguments);

        if (!empty($this->events['after'])) {
            if (is_array($this->events['after'])) {
                foreach ($this->events['after'] as $before) {
                    $eventHandler->fireEvent($before);
                }
            } else {
                $eventHandler->fireEvent($this->events['after']);
            }
        }

        return $res;
    }

    /**
     * @throws ReflectionException
     */
    private function _createCallback(array $callback): ?callable
    {
        [$class, $method] = $callback;
        if (!is_object($class) && class_exists($class)) {
            $class = Container::getInstance()->createObject($class);
        }

        if (!is_object($class)) {
            return null;
        }

        return [$class, $method];
    }
}
