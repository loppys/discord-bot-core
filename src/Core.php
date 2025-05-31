<?php

namespace Discord\Bot;

use Discord\Bot\System\Discord\DiscordEventManager;
use Discord\Bot\System\Params;
use Vengine\Libraries\DBAL\Adapter;
use Discord\Bot\System\Events\EventDispatcher;
use Vengine\Libraries\Console\ConsoleLogger;
use Discord\Bot\System\Interfaces\ComponentLicenseInterface;
use Discord\Bot\System\License\DTO\ComponentInfo;
use Discord\Bot\System\License\DTO\KeyPeriod;
use Discord\Bot\System\License\LicenseInjection;
use Discord\Bot\System\License\LicenseManager;
use Discord\Bot\System\License\Storages\ActivateMethodStorage;
use Discord\Bot\System\Logger;
use Discord\Bot\System\Migration\MigrationManager;
use Discord\Bot\System\Traits\ContainerInjection;
use Discord\Bot\System\Traits\SingletonTrait;
use Discord\Bot\Scheduler\ScheduleManager;
use Discord\Bot\System\ComponentsFacade;
use Discord\Bot\System\Interfaces\SingletonInterface;
use Discord\Discord;
use Discord\Exceptions\IntentException;
use Loader\System\Container;
use React\EventLoop\LoopInterface;
use RuntimeException;
use ReflectionException;
use Vengine\Libraries\DBAL\DTO\Config as DatabaseConfig;

class Core implements SingletonInterface, ComponentLicenseInterface
{
    use SingletonTrait;
    use ContainerInjection;
    use LicenseInjection;

    protected Discord $discord;

    protected LoopInterface $loop;

    public function __construct(
        MigrationManager $migrationManager,
        ScheduleManager $scheduleManager,
        DiscordEventManager $discordEventManager,
        Adapter $db
    ) {
        if (empty($_SERVER['create.auto'])) {
            trigger_error(
                'Manual creation of an object is not recommended, use the create() method.',
                E_USER_WARNING
            );
        }

        if (!empty(static::$instance)) {
            throw new RuntimeException('Creation of a second copy is prohibited. use Core::getInstance()');
        }

        $this->getContainer()
            ->setShared('scheduleManager', $scheduleManager)
            ->setShared('migrationManager', $migrationManager)
            ->setShared('discordEventManager', $discordEventManager)
            ->setShared('db', $db)
        ;

        static::$instance = $this;
    }

    public static function getInstance(): static
    {
        /** @var Core $instance */
        $instance = self::$instance;

        if (empty($instance)) {
            throw new RuntimeException('Core not init');
        }

        return $instance;
    }

    public static function hasInstance(): bool
    {
        return self::$instance !== null;
    }

    /**
     * @throws ReflectionException
     * @throws IntentException
     */
    public static function create(Configurator $configurator): static
    {
        if (static::hasInstance()) {
            return static::getInstance();
        }

        ConsoleLogger::showMessage('start core create');

        $_SERVER['create.auto'] = true;
        $_SERVER['core.dir'] = __DIR__;

        $globalConfigPath = $configurator->getGlobalConfigPath();
        $discordOptions = $configurator->getDiscordOptions();
        $overrideComponents = $configurator->getOverrideComponents();
        $initDI = $configurator->isInitDI();

        if (!file_exists($globalConfigPath)) {
            throw new RuntimeException('config file empty');
        }

        if (pathinfo($globalConfigPath, PATHINFO_EXTENSION) !== 'php') {
            throw new RuntimeException('config must be with php extension');
        }

        $globalConfig = require $globalConfigPath;

        if (!is_array($globalConfig)) {
            throw new RuntimeException('invalid config');
        }

        $globalConfigObject = Params::create($globalConfig);

        $dbParams = $globalConfigObject->get('databaseParams');
        if (empty($dbParams) || !is_array($dbParams)) {
            throw new RuntimeException('db params invalid');
        }

        DatabaseConfig::setDatabaseParams($dbParams);
        Config::setSymbolCommand($globalConfigObject->get('symbolCommand', '~'));
        Config::setUseNewCommandSystem($globalConfigObject->get('useNewCommandSystem', true));

        $ds = DIRECTORY_SEPARATOR;
        $_SERVER['install.dir'] = $globalConfigObject->get(
            'install.dir',
            __DIR__ . $ds . 'Migrations' . $ds . 'install' . $ds
        );

        if ($initDI) {
            new Container();
        }

        if (empty($discordOptions)) {
            throw new RuntimeException('discord options empty');
        }

        $discord = new Discord($discordOptions);

        /** @var Core $core */
        $core = Container::getInstance()->createObject(static::class);

        if (!is_object($core)) {
            throw new RuntimeException('Create core fail');
        }

        $core->setLogger(
            $core->getContainer()->createObject(Logger::class)
        );

        $core->setLicenseManager(
            $core->getContainer()->createObject(LicenseManager::class)
        );

        $core->setEventDispatcher(
            $core->getContainer()->createObject(EventDispatcher::class)
        );

        $core->getContainer()->setShared(
            'components', $core->getContainer()->createObject(ComponentsFacade::class)
        );

        $core->setDiscord($discord);

        ConsoleLogger::showMessage('----------');
        ConsoleLogger::showMessage('init default components');
        $core->components->initComponents();
        ConsoleLogger::showMessage('----------');
        ConsoleLogger::showMessage('default components created');
        ConsoleLogger::showMessage('----------');

        $core->scheduleManager->setLoop(
            $core->getLoop()
        );

        if (!empty($overrideComponents)) {
            ConsoleLogger::showMessage('----------');
            ConsoleLogger::showMessage('init override components');
            $core->components->overrideClassList($overrideComponents);
            ConsoleLogger::showMessage('----------');
            ConsoleLogger::showMessage('override components created');
            ConsoleLogger::showMessage('----------');
        }

        $core->discordEventManager
            ->initDiscord($discord)
            ->initDefaultEvents()
        ;

        if (!empty($configurator->getDiscordEvents())) {
            foreach ($configurator->getDiscordEvents() as $name => $class) {
                $core->discordEventManager->registerDiscordEvent($name, $class);
            }
        }

        $core->baseActivateComponent('default');

        ConsoleLogger::showMessage('core init success');

        return $core;
    }

    public function run(): void
    {
        if (empty($this->discord)) {
            throw new RuntimeException('discord not init');
        }

        if (empty($this->loop)) {
            $this->loop = $this->discord->getLoop();
        }

        $this->scheduleManager->start();

        ConsoleLogger::showMessage('app run');

        $this->discord->run();
    }

    /**
     * @throws ReflectionException
     * @throws IntentException
     */
    public function restart(?Configurator $configurator): void
    {
        ConsoleLogger::showMessage(PHP_EOL . 'Restart app' . PHP_EOL);

        $this->discord->close();

        $this->scheduleManager->stop();
        $this->db->reConnect();
        $this->discordEventManager->reset();

        Container::getInstance()->getObjectStorage()->delete(strtolower(static::class));

        static::$instance = null;

        $configurator->setInitDI(true);

        ConsoleLogger::showMessage(PHP_EOL . 'App reset done' . PHP_EOL);

        static::create($configurator)->run();
    }

    public function getDiscord(): Discord
    {
        if (empty($this->discord)) {
            throw new RuntimeException('discord not init');
        }

        return $this->discord;
    }

    public function setDiscord(Discord $discord): void
    {
        $this->discord = $discord;
        $this->loop = $discord->getLoop();
    }

    public function getComponentFacade(): ComponentsFacade
    {
        return $this->components;
    }

    public function getScheduleManager(): ScheduleManager
    {
        return $this->scheduleManager;
    }

    public function getDatabaseAdapter(): Adapter
    {
        return $this->db;
    }

    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }

    public function setEventDispatcher(EventDispatcher $eventDispatcher): static
    {
        ConsoleLogger::showMessage('init eventDispatcher');

        $this->getContainer()->setShared('eventDispatcher', $eventDispatcher);

        return $this;
    }

    public function setLogger(Logger $logger): static
    {
        ConsoleLogger::showMessage('init logger');

        $this->getContainer()->setShared('logger', $logger);

        return $this;
    }

    public function setLicenseManager(LicenseManager $licenseManager): static
    {
        ConsoleLogger::showMessage('init licenseManager');

        $this->getContainer()->setShared('licenseManager', $licenseManager);

        return $this;
    }

    public function writeLog(string $value): bool
    {
        return $this->logger->write($value, 'core');
    }

    public function baseActivateComponent(string $guild = 'default'): void
    {
        $this->_licenseInjection($guild);

        $key = $this->getKey($guild, $this->getComponentName());

        if ($key === null) {
            $newKey = $this->licenseManager->getActivationService()
                ->newKey(
                    $guild,
                    KeyPeriod::createDefault(time(), strtotime('+30 days')),
                    master: true
                )
            ;

            $componentInfo = (new ComponentInfo())
                ->setComponentName($this->getComponentName())
                ->setComponentClass(static::class)
                ->setUseComponentClass(true)
            ;

            $newKey->setComponentInfo($componentInfo);

            $this->licenseManager->activate($newKey, ActivateMethodStorage::MAIN);
        }
    }

    public function getComponentName(): string
    {
        return 'core';
    }

    public function guildKeyValid(string $guild = ''): bool
    {
        return true;
    }

    public function keyRequired(): bool
    {
        return true;
    }
}
