<?php

namespace Discord\Bot\Components;

use Discord\Bot\Components\Command\DTO\CommandMigration;
use Discord\Bot\Components\Command\Services\CommandService;
use Discord\Bot\Scheduler\Parts\DefaultTask;
use Discord\Bot\Scheduler\Parts\Executor;
use Discord\Bot\Scheduler\Storage\QueueGroupStorage;
use Discord\Bot\System\Events\AbstractSystemEventHandle;
use Discord\Bot\System\Interfaces\ComponentInterface;
use Discord\Bot\System\ComponentsFacade;
use Discord\Discord;
use Doctrine\DBAL\Exception;
use Discord\Bot\Core;
use ReflectionException;

abstract class AbstractComponent extends AbstractSystemEventHandle implements ComponentInterface
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
     * @var array<CommandMigration>
     */
    protected array $commands = [];

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function __construct(mixed $service)
    {
        parent::__construct();

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
            if (!is_string($name)) {
                $name = null;
            }

            $core->scheduleManager->initTaskByArray(
                $scheduleTask,
                is_int($name) ? '' : ($name ?? '')
            );
        }

        if (!empty($this->commands)) {
            if (!$this->components->isCreated('command')) {
                $executor = (new Executor())
                    ->setCallable([CommandService::class, 'executeCommandMigration'])
                    ->setArguments($this->commands)
                ;

                $task = (new DefaultTask())
                    ->setName('component-commands')
                    ->setExecutor($executor)
                    ->setQueueGroup(QueueGroupStorage::FIRST)
                ;

                $core->scheduleManager->addTask($task);
            } else {
                foreach ($this->commands as $command) {
                    $this->components->command
                        ->getService()
                        ->addCommandMigration($command)
                    ;
                }
            }
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

    public function getService(): mixed
    {
        return $this->service;
    }
}
