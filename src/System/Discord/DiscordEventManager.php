<?php

namespace Discord\Bot\System\Discord;

use Discord\Bot\Core;
use Discord\Discord;
use Discord\Bot\System\Discord\Events\AbstractEvent;
use Discord\Bot\System\Traits\ContainerInjection;
use Vengine\Libraries\Console\ConsoleLogger;
use Vengine\Libs\DI\interfaces\ContainerAwareInterface;

class DiscordEventManager implements ContainerAwareInterface
{
    use ContainerInjection;

    /**
     * @var array<string>
     */
    protected array $defaultEvents = [];

    /**
     * @var array<AbstractEvent>
     */
    protected array $registeredEventList = [];

    public function initDefaultEvents(): static
    {
        if (empty($this->discord)) {
            return $this;
        }

        foreach ($this->defaultEvents as $name => $event) {
            $this->registerDiscordEvent($name, $event);
        }

        return $this;
    }

    public function registerDiscordEvent(string $eventName, string $eventClass): static
    {
        if (!empty($this->registeredEventList[$eventName])) {
            return $this;
        }

        $event = new $eventClass;

        if (!$event instanceof AbstractEvent) {
            return $this;
        }

        $event->setComponents(
            Core::getInstance()->components
        );

        $this->discord->on($eventName, $event->getCallable());

        $this->registeredEventList[$eventName] = $event;

        ConsoleLogger::showMessage("register discord event: {$eventName}");

        return $this;
    }

    public function reset(): void
    {
        ConsoleLogger::showMessage('reset discord events');

        $this->defaultEvents = [];
        $this->registeredEventList = [];
    }
}
