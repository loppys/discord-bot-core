<?php

namespace Discord\Bot\Scheduler\Interface;

interface ExecutorInterface
{
    public function setCallable(callable $callable): static;

    public function setArguments(array $arguments): static;

    public function getCallable(): callable;

    public function getArguments(): array;

    public function execute(): bool;

    public function getInstance(): static;
}
