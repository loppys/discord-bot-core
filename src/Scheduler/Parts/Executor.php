<?php

namespace Discord\Bot\Scheduler\Parts;

use Discord\Bot\Scheduler\Interface\ExecutorInterface;

class Executor implements ExecutorInterface
{
    protected mixed $callable;

    protected array $arguments = [];

    public function setCallable(callable $callable): static
    {
        $this->callable = $callable;

        return $this;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }

    public function setArguments(array $arguments): static
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function execute(): bool
    {
        return call_user_func_array($this->getCallable(), $this->getArguments());
    }

    public function getInstance(): static
    {
        return $this;
    }
}
