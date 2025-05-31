<?php

namespace Discord\Bot\System\Events;

use Discord\Bot\Core;
use Discord\Bot\System\Traits\ContainerInjection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Discord\Bot\System\Events\Interfaces\EventListenerInterface;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\DI\Exceptions\NotFoundException;
use Vengine\Libs\DI\interfaces\ContainerAwareInterface;

abstract class AbstractSystemEventHandle implements ContainerAwareInterface
{
    use ContainerInjection;

    protected EventDispatcher $eventDispatcher;

    /**
     * @var EventListenerInterface[]|string[]
     */
    protected array $_events = [];

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function __construct()
    {
        $this->setContainer(Core::getInstance()->getContainer());

        /** @var EventDispatcher $eventDispatcher */
        $this->eventDispatcher = $this->container->get('eventDispatcher');

        foreach ($this->_events as $listener => $item) {
            if (is_string($listener)) {
                if (class_exists($listener)) {
                    $listener = $this->container->get($listener);
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

        $method = ucfirst($name);

        $this->eventDispatcher->dispatch("before.{$name}", "before{$method}", $arguments);

        $result = call_user_func_array([$this, "{$name}Eventable"], $arguments);

        $this->eventDispatcher->dispatch("after.{$name}", "after{$method}", $arguments);

        return $result;
    }
}
