<?php

namespace Discord\Bot\Components;

use Discord\Bot\Core;
use Discord\Bot\System\ComponentsFacade;
use Discord\Bot\System\Interfaces\ComponentInterface;
use Discord\Discord;
use Doctrine\DBAL\Exception;

abstract class AbstractComponent implements ComponentInterface
{
    protected ComponentsFacade $components;

    protected Discord $discord;

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
    public function __construct(mixed $service)
    {
        $core = Core::getInstance();

        $this->service = $service;
        $this->discord = $core->getDiscord();
        $this->components = $core->components;

        foreach ($this->migrationList as $migrationLink) {
            if (is_dir($migrationLink)) {
                $core->migrationManager->collectMigrationFiles($migrationLink, force: $this->forceRunMigrations);

                continue;
            }

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

    abstract public function getService(): mixed;
}
