<?php

namespace App\System\Discord\Events;

use Loader\System\Traits\ContainerTrait;

abstract class AbstractEvent
{
    use ContainerTrait;

    protected string $name = '';

    /**
     * @var array<string>
     */
    protected array $methodList = [];

    public function __construct()
    {
        $this->container = $this->getContainer();
    }

    public function getCallable(?string $name = null): ?callable
    {
        if (empty($this->methodList[$name ?? $this->name])) {
            return null;
        }

        $method = $this->methodList[$name ?? $this->name];
        if (!method_exists($this, $method)) {
            return null;
        }

        return [$this, $method];
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
