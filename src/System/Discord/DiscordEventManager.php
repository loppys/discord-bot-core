<?php

namespace Discord\Bot\System\Discord;

use Discord\Bot\Core;
use Discord\Bot\System\Discord\Events\AbstractEvent;
use Vengine\Libraries\Console\ConsoleLogger;
use Loader\System\Traits\ContainerTrait;
use Discord\Discord;

class DiscordEventManager
{
    use ContainerTrait;

    protected Discord $discord;

    /**
     * @var array<string>
     */
    protected array $defaultEvents = [];

    /**
     * @var array<AbstractEvent>
     */
    protected array $registeredEventList = [];

    public function initDiscord(Discord $discord): static
    {
        $this->discord = $discord;

        return $this;
    }

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
