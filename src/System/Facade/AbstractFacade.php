<?php

namespace Discord\Bot\System\Facade;

use Discord\Bot\System\Traits\SystemStatAccessTrait;

abstract class AbstractFacade
{
    use SystemStatAccessTrait;

    private ClassFacade $facade;

    protected array $initClassList = [];

    public function __construct(bool $initClassList = true)
    {
        $this->facade = new ClassFacade();

        if ($initClassList) {
            $this->facade->initClassList($this->initClassList);
        }
    }

    public function add(string $name, object|string $value): static
    {
        $this->facade->add($name, $value);

        return $this;
    }

    public function get(string $name): mixed
    {
        $this
            ->getSystemStat()
            ->addNewStat($name, "use component `{$name}`")
            ->add($name)
        ;

        return $this->facade->get($name);
    }

    public function isCreated(string $name): bool
    {
        return $this->facade->isCreated($name);
    }

    public function __get($name): mixed
    {
        $this
            ->getSystemStat()
            ->addNewStat($name, "use component `{$name}`")
            ->add($name)
        ;

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

    public function getAll(): array
    {
        return $this->facade->getAll();
    }
}
