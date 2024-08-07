<?php

namespace Discord\Bot\System\Facade;

abstract class AbstractFacade
{
    private ClassFacade $facade;

    protected array $initClassList = [];

    public function __construct()
    {
        $this->facade = (new ClassFacade())->initClassList($this->initClassList);
    }

    public function add(string $name, object|string $value): static
    {
        $this->facade->add($name, $value);

        return $this;
    }

    public function __get($name): mixed
    {
        return $this->facade->get($name);
    }

    public function __set(string $name, $value): void
    {
        $this->facade->overrideClass($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->facade->has($name);
    }

    public function overrideClassList(array $classList): static
    {
        $this->facade->overrideClassList($classList);

        return $this;
    }

    public function overrideClass(string $name, string $class): static
    {
        $this->facade->overrideClass($name, $class);

        return $this;
    }

    public function getClassList(): array
    {
        return $this->facade->getClassList();
    }
}
