<?php

namespace Discord\Bot\System\Discord\Events;

use Discord\Bot\System\ComponentsFacade;
use Discord\Bot\System\Discord\Interfaces\EventExtensionInterface;
use Discord\Bot\System\Traits\ContainerInjection;
use Vengine\Libs\DI\interfaces\ContainerAwareInterface;

abstract class AbstractEvent implements ContainerAwareInterface
{
    use ContainerInjection;

    protected ComponentsFacade $components;

    protected string $name = '';

    protected string $callbackMethod;

    public function setComponents(ComponentsFacade $components): static
    {
        $this->components = $components;

        if ($this instanceof EventExtensionInterface) {
            foreach ($this->initComponentsInProperty() as $property => $componentName) {
                if ($component = $this->components->get($componentName)) {
                    if (property_exists($this, $property)) {
                        $this->{$property} = $component;
                    }
                }
            }
        }

        return $this;
    }

    public function getCallable(): ?callable
    {
        if (!method_exists($this, $this->callbackMethod ?? '')) {
            return null;
        }

        return [$this, $this->callbackMethod];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    protected function success(): bool
    {
        return true;
    }

    protected function fail(): bool
    {
        return false;
    }
}
