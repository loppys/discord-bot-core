<?php

namespace Discord\Bot\Scheduler\Parts;

use Discord\Bot\Scheduler\Interface\InstanceAccessInterface;
use Discord\Bot\Scheduler\Interface\TaskExecuteInterface;
use Discord\Bot\Scheduler\Interface\TaskInterface;
use Discord\Bot\Scheduler\Storage\QueueGroupStorage;
use Discord\Bot\Scheduler\Storage\TaskTypeStorage;
use Loader\System\Container;

abstract class AbstractTask implements TaskInterface, TaskExecuteInterface, InstanceAccessInterface
{
    protected string $name = '';

    protected int $type = TaskTypeStorage::EMPTY;

    protected string $queueGroup = QueueGroupStorage::DEFAULT;

    protected Executor $executor;

    public function __construct()
    {
        if (empty($this->name)) {
            $this->setName(
                uniqid('task.', true)
            );
        }
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type = TaskTypeStorage::EMPTY): static
    {
        $this->type = $type;

        return $this;
    }

    public function getClass(): string
    {
        return static::class;
    }

    public function getInstance(): static
    {
        return $this;
    }

    public function setExecutor(Executor $executor): static
    {
        $this->executor = $executor;

        return $this;
    }

    public function getExecutor(): Executor
    {
        if (empty($this->executor)) {
            $this->executor = (new Executor())->setCallable([$this, 'execute']);
        }

        [$object, $method] = $this->executor->getCallable();

        if (!is_object($object)) {
            if (class_exists($object)) {
                $object = Container::getInstance()->createObject($object);
            } else {
                $object = $this;
            }

            if (!method_exists($object, $method)) {
                $method = 'execute';

                $this->executor->setArguments([]);
            }

            $this->executor->setCallable([$object, $method]);
        }

        return $this->executor;
    }

    public function execute(): bool
    {
        trigger_error('Attempt to call an empty method. execute method is not overridden.');

        return false;
    }

    public function getQueueGroup(): string
    {
        return $this->queueGroup;
    }

    public function setQueueGroup(string $group): static
    {
        $this->queueGroup = $group;

        return $this;
    }
}
