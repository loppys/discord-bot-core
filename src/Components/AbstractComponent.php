<?php

namespace Discord\Bot\Components;

use Discord\Bot\Components\Command\DTO\CommandMigration;
use Discord\Bot\Components\Command\Services\CommandService;
use Discord\Bot\Components\Settings\Entity\Setting;
use Discord\Bot\Components\Settings\SettingsComponent;
use Discord\Bot\Scheduler\Parts\DefaultTask;
use Discord\Bot\Scheduler\Parts\Executor;
use Discord\Bot\Scheduler\Storage\QueueGroupStorage;
use Discord\Bot\System\Events\AbstractSystemEventHandle;
use Discord\Bot\System\GlobalRepository\Traits\LogSourceTrait;
use Vengine\Libraries\Console\ConsoleLogger;
use Discord\Bot\System\Interfaces\ComponentInterface;
use Discord\Bot\System\ComponentsFacade;
use Discord\Bot\System\License\DTO\ComponentInfo;
use Discord\Bot\System\License\DTO\KeyPeriod;
use Discord\Bot\System\License\LicenseInjection;
use Discord\Bot\System\License\LicenseManager;
use Discord\Bot\System\License\Storages\ActivateMethodStorage;
use Discord\Bot\System\License\Storages\KeyPrefixStorage;
use Discord\Bot\System\Storages\TypeSystemStat;
use Discord\Bot\System\Traits\SystemStatAccessTrait;
use Discord\Discord;
use Doctrine\DBAL\Exception;
use Discord\Bot\Core;
use Loader\System\Container;
use ReflectionException;

abstract class AbstractComponent extends AbstractSystemEventHandle implements ComponentInterface
{
    use SystemStatAccessTrait;
    use LogSourceTrait;
    use LicenseInjection;

    protected string $name = '';

    protected ComponentsFacade $components;

    protected Discord $discord;

    protected mixed $service;

    protected string $mainServiceClass = '';

    /**
     * @see ['propertyName' => 'serviceClass']
     */
    protected array $additionServices = [];

    /**
     * @var array<Setting|array{
     *     stg_guild:string,
     *     stg_name:string,
     *     stg_value:string,
     *     stg_type:string,
     *     stg_enabled:bool,
     *     stg_required:bool,
     *     stg_system:bool,
     *     stg_hidden:bool
     * }>
     */
    protected array $settings = [];

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
     * @var array<CommandMigration|string>
     */
    protected array $commands = [];

    protected LicenseManager $licenseManager;

    protected bool $keyRequired = false;

    protected Core $core;

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function __construct(mixed $service = null)
    {
        parent::__construct();

        $this->core = $core = Core::getInstance();

        if ($service !== null) {
            $this->service = $service;
        }

        $this->discord = $core->getDiscord();
        $this->components = $core->components;
        $this->licenseManager = $core->licenseManager;

        foreach ($this->migrationList as $migrationLink) {
            if (is_dir($migrationLink)) {
                $core->migrationManager->collectMigrationFiles($migrationLink, force: $this->forceRunMigrations);

                continue;
            }

            $query = $core->migrationManager->createMigrationQuery($migrationLink);

            if ($this->forceRunMigrations && $query !== null) {
                if ($core->migrationManager->migrationExecute($query)) {
                    $core->migrationManager->removeMigrationByQuery($query);
                }
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
            $currentClass = static::class;
            ConsoleLogger::mixedMessage(
                "add command migrations: {$currentClass}",
                $this->commands
            );

            if (!$this->components->isCreated('command')) {
                $executor = (new Executor())
                    ->setCallable([CommandService::class, 'executeCommandMigration'])
                    ->setArguments($this->commands)
                ;

                $task = (new DefaultTask())
                    ->setName('migration-commands:' . static::class)
                    ->setExecutor($executor)
                    ->setQueueGroup(QueueGroupStorage::FIRST)
                ;

                $core->scheduleManager->addTask($task);
            } else {
                foreach ($this->commands as $command) {
                    if (!is_object($command)) {
                        $command = $core->getContainer()->createObject($command);
                    }

                    if (!$command instanceof CommandMigration) {
                        continue;
                    }

                    $this->components->command
                        ->getService()
                        ->addCommandMigration($command)
                    ;
                }
            }
        }

        if (
            empty($this->service)
            && !empty($this->mainServiceClass)
            && class_exists($this->mainServiceClass)
        ) {
            $this->service = $core->getContainer()->createObject($this->mainServiceClass);
        }

        foreach ($this->additionServices as $propertyName => $serviceClass) {
            if (empty($propertyName) || !class_exists($serviceClass)) {
                continue;
            }

            $setter = 'set' . ucfirst($propertyName);
            $serviceObject = $core->getContainer()->createObject($serviceClass);

            if (method_exists($this, $setter)) {
                $this->{$setter}($serviceObject);
            } elseif (property_exists($this, $propertyName)) {
                $this->{$propertyName} = $serviceObject;
            }
        }

        foreach ($this->settings as $setting) {
            if ($this instanceof SettingsComponent) {
                $this->addSetting($setting);
            } else {
                $this->components->settings->addSetting($setting);
            }
        }

        $this->getSystemStat()->add(TypeSystemStat::COMPONENT);
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
        if (empty($this->service)) {
            return null;
        }

        return $this->service;
    }

    public function keyRequired(): bool
    {
        return $this->keyRequired;
    }

    /**
     * @throws Exception
     */
    public function guildKeyValid(string $guild = ''): bool
    {
        if (!$this->keyRequired) {
            return true;
        }

        $key = $this->licenseManager->getKey(
            $guild,
            KeyPrefixStorage::COMPONENT,
            $this->name
        );

        if ($key === null) {
            return false;
        }

        return !$key->isExpired();
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        $this->setLogSource($name);

        return $this;
    }

    public function baseActivateComponent(string $guild = 'default'): void
    {
        $this->_licenseInjection($guild);

        if ($this instanceof SettingsComponent) {
            $setting = $this->getService()->getSettingByName('useLicense', $guild);
        } else {
            $setting = $this->components->settings->getService()->getSettingByName('useLicense', $guild);
        }

        if ($setting !== null && $setting->stg_enabled) {
            $this->useLicense = $setting->stg_value;
        }

        $key = $this->getKey($guild, $this->getComponentName());

        if ($key === null) {
            $componentInfo = (new ComponentInfo())
                ->setComponentName($this->name ?? static::class)
                ->setUseComponentClass(true)
            ;

            $newKey = $this->licenseManager->getActivationService()
                ->newKey(
                    'default',
                    KeyPeriod::createInfinity(),
                    $componentInfo
                )
            ;

            $this->licenseManager->activate($newKey, ActivateMethodStorage::COMPONENT);
        }
    }

    public function getComponentName(): string
    {
        return $this->name;
    }

    public function licenseInjection(string $guild): void
    {
        $this->_licenseInjection($guild);
    }
}
