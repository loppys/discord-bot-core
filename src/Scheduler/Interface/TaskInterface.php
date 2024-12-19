<?php

namespace Discord\Bot\Scheduler\Interface;

use Discord\Bot\Scheduler\Parts\Executor;
use Discord\Bot\Scheduler\Storage\ExecuteSchemeStorage;

interface TaskInterface extends QueueTaskInterface
{
    public function setName(string $name): static;

    public function getName(): string;

    public function getType(): int;

    public function setType(int $type): static;

    public function setExecutor(Executor $executor): static;

    public function getExecutor(): Executor;

    public function isDone(): bool;

    public function done(): static;

    public function defineExecuteScheme(int $scheme = ExecuteSchemeStorage::AUTO): static;
}
