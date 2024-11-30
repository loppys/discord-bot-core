<?php

namespace Discord\Bot\Scheduler\Interface;

use Discord\Bot\Scheduler\Parts\Executor;

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

    public function defineExecuteScheme(int $scheme = 1): static;
}
