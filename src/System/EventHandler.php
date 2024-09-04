<?php

namespace Discord\Bot\System;

class EventHandler
{
    /**
     * @var callable[]
     */
    protected array $events = [];

    /**
     * @var string[]
     */
    protected array $activityEvents = [];

    public function registerEvent(string $name, callable $callback): static
    {
        if (!empty($this->events[$name])) {
            return $this;
        }

        $this->events[$name] = $callback;
        $this->activityEvents[$name] = $name;

        return $this;
    }

    public function disableEvent(string $name): bool
    {
        if (empty($this->activityEvents[$name])) {
            return false;
        }

        unset($this->activityEvents[$name]);

        return true;
    }

    public function deleteEvent(string $name): bool
    {
        if (empty($this->events[$name])) {
            return false;
        }

        unset($this->events[$name], $this->activityEvents[$name]);

        return true;
    }

    public function fireEvent(string $name): void
    {
        if (empty($this->activityEvents[$name]) || empty($this->events[$name])) {
            return;
        }

        call_user_func($this->events[$name]);
    }
}
