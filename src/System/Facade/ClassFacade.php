<?php

namespace Discord\Bot\System\Facade;

use Loader\System\Traits\ContainerTrait;
use RuntimeException;

class ClassFacade
{
    use ContainerTrait;

    /**
     * @var array<string>
     */
    protected array $classList = [];

    protected array $created = [];

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, $value): void
    {
        $this->add($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    public function initClassList(array $classList): static
    {
        $this->classList = $classList;

        return $this;
    }

    public function overrideClassList(array $classList): static
    {
        foreach ($classList as $name => $class) {
            $this->overrideClass($name, $class);
        }

        return $this;
    }

    public function overrideClass(string $name, string $class): static
    {
        $this->classList[$name] = $class;

        return $this;
    }

    public function add(string $name, string|object $class): static
    {
        if ($this->has($name)) {
            return $this;
        }

        if (is_object($class)) {
            $this->classList[$name] = $class::class;
            $this->created[$name] = $class;
        } else {
            $this->classList[$name] = $class;

            $this->create($name);
        }

        return $this;
    }

    public function has(string $name): bool
    {
        return !empty($this->classList[$name]);
    }

    public function get(string $name): mixed
    {
        if (empty($this->created[$name])) {
            $this->create($name);
        }

        return $this->created[$name];
    }

    public function getClassList(): array
    {
        return $this->classList;
    }

    protected function create(string $name): void
    {
        $class = $this->getClassByName($name);

        if ($class === null) {
            $currentFacadeClass = static::class;

            throw new RuntimeException("Facade {$currentFacadeClass}: class not found for {$name}");
        }

        if (!empty($this->created[$name]) && is_object($this->created[$name])) {
            return;
        }

        $this->created[$name] = $this->getContainer()->createObject($class);
    }

    protected function getClassByName(string $name): ?string
    {
        return $this->classList[$name] ?? null;
    }
}
