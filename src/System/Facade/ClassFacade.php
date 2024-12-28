<?php

namespace Discord\Bot\System\Facade;

use Vengine\Libraries\Console\ConsoleLogger;
use Discord\Bot\System\Storages\TypeSystemStat;
use Discord\Bot\System\Traits\SystemStatAccessTrait;
use Loader\System\Traits\ContainerTrait;
use RuntimeException;

class ClassFacade
{
    use ContainerTrait;
    use SystemStatAccessTrait;

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

        ConsoleLogger::showMessage('----------');
        ConsoleLogger::showMessage("overridden {$name} in Facade");

        $this->create($name);

        return $this;
    }

    public function add(string $name, string|object $class): static
    {
        if ($this->has($name)) {
            return $this;
        }

        ConsoleLogger::showMessage('----------');
        ConsoleLogger::showMessage("add {$name} in Facade");

        if (is_object($class)) {
            $this->classList[$name] = $class::class;
            $this->created[$name] = $class;
        } else {
            $this->classList[$name] = $class;

            $this->create($name);
        }

        return $this;
    }

    public function isCreated(string $name): bool
    {
        return !empty($this->created[$name]);
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

    public function getAll(): array
    {
        $res = [];

        foreach ($this->getClassList() as $name => $class) {
            $res[] = $this->get($name);
        }

        return $res;
    }

    public function getClassList(): array
    {
        return $this->classList;
    }

    protected function create(string $name): void
    {
        $class = $this->getClassByName($name);

        if ($class === null) {
            throw new RuntimeException("class not found for {$name}");
        }

        if (!empty($this->created[$name]) && is_object($this->created[$name])) {
            return;
        }

        $object = $this->getContainer()->createObject($class);

        if (method_exists($object, 'setName')) {
            call_user_func([$object, 'setName'], $name);
        }

        if (method_exists($object, 'baseActivateComponent')) {
            call_user_func([$object, 'baseActivateComponent']);
        }

        $this->created[$name] = $object;

        ConsoleLogger::showMessage("{$name} created");
    }

    protected function getClassByName(string $name): ?string
    {
        return $this->classList[$name] ?? null;
    }
}
