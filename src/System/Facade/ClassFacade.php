<?php

namespace Discord\Bot\System\Facade;

use Discord\Bot\Core;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Vengine\Libraries\Console\ConsoleLogger;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\DI\Exceptions\NotFoundException;
use Vengine\Libs\DI\interfaces\ContainerAwareInterface;
use Vengine\Libs\DI\traits\ContainerAwareTrait;
use RuntimeException;

class ClassFacade implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var array<string>
     */
    protected array $classList = [];

    protected array $created = [];

    /**
     * @throws ContainerException
     */
    public function __construct()
    {
        $this->setContainer(Core::getInstance()->getContainer());
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
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

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function overrideClassList(array $classList): static
    {
        foreach ($classList as $name => $class) {
            $this->overrideClass($name, $class);
        }

        return $this;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function overrideClass(string $name, string $class): static
    {
        $this->classList[$name] = $class;

        ConsoleLogger::showMessage('----------');
        ConsoleLogger::showMessage("overridden {$name} in Facade");

        $this->create($name);

        return $this;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
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

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
    protected function create(string $name): void
    {
        $class = $this->getClassByName($name);

        if ($class === null) {
            throw new RuntimeException("class not found for {$name}");
        }

        if (!empty($this->created[$name]) && is_object($this->created[$name])) {
            return;
        }

        ConsoleLogger::showMessage("create object: {$name}");
        $object = $this->getContainer()->get($class);
        ConsoleLogger::showMessage("object created: {$name}");

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
