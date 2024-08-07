<?php

namespace Discord\Bot\Components;

use Discord\Bot\System\Interfaces\ComponentInterface;

abstract class AbstractComponent implements ComponentInterface
{
    protected mixed $repository;

    protected mixed $service;

    protected array $scheduleTasks = [];

    protected array $migrationList = [];

    public function __construct(mixed $repository, mixed $service)
    {
        $this->repository = $repository;
        $this->service = $service;
    }

    /**
     * @inheritDoc
     */
    public function getScheduleTasks(): array
    {
        return $this->scheduleTasks;
    }

    public function getMigrationList(): array
    {
        return $this->migrationList;
    }

    /**
     * @inheritDoc
     */
    abstract public function getRepository(): mixed;

    /**
     * @inheritDoc
     */
    abstract public function getService(): mixed;
}
