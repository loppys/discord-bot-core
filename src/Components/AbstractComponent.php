<?php

namespace Discord\Bot\Components;

use Discord\Bot\Core;
use Discord\Bot\System\Interfaces\ComponentInterface;
use Doctrine\DBAL\Exception;

abstract class AbstractComponent implements ComponentInterface
{
    protected mixed $repository;

    protected mixed $service;

    protected bool $forceRunMigrations = true;

    /**
     * @var array<array>
     */
    protected array $scheduleTasks = [];

    /**
     * @var array<string>
     */
    protected array $migrationList = [];

    /**
     * @throws Exception
     */
    public function __construct(mixed $repository, mixed $service)
    {
        $this->repository = $repository;
        $this->service = $service;

        $core = Core::getInstance();

        foreach ($this->migrationList as $migrationLink) {
            $query = $core->migrationManager->createMigrationQuery($migrationLink);

            if ($this->forceRunMigrations && $query !== null) {
                $core->migrationManager->migrationExecute($query);
            }
        }

        foreach ($this->scheduleTasks as $name => $scheduleTask) {
            $core->scheduleManager->initTaskByArray($scheduleTask, $name ?? '');
        }
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
