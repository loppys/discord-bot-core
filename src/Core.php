<?php

namespace Discord\Bot;

use Discord\Bot\System\Discord\DiscordEventManager;
use Discord\Bot\System\DBAL;
use Discord\Bot\System\Events\EventDispatcher;
use Discord\Bot\System\Helpers\ConsoleLogger;
use Discord\Bot\System\License\DTO\ComponentInfo;
use Discord\Bot\System\License\DTO\KeyPeriod;
use Discord\Bot\System\License\LicenseManager;
use Discord\Bot\System\License\Storages\ActivateMethodStorage;
use Discord\Bot\System\License\Storages\KeyPrefixStorage;
use Discord\Bot\System\Logger;
use Discord\Bot\System\Migration\MigrationManager;
use Discord\Bot\System\Storages\TypeSystemStat;
use Discord\Bot\System\SystemStat;
use Discord\Bot\System\Traits\ContainerInjection;
use Discord\Bot\System\Traits\SingletonTrait;
use Discord\Bot\Scheduler\ScheduleManager;
use Discord\Bot\System\ComponentsFacade;
use Discord\Bot\System\Interfaces\SingletonInterface;
use Discord\Discord;
use Discord\Exceptions\IntentException;
use Doctrine\DBAL\Exception;
use Loader\System\Container;
use React\EventLoop\LoopInterface;
use RuntimeException;
use ReflectionException;

class Core implements SingletonInterface
{
    use SingletonTrait;
    use ContainerInjection;

    protected Discord $discord;

    protected LoopInterface $loop;

    public function __construct(
        MigrationManager $migrationManager,
        ScheduleManager $scheduleManager,
        DiscordEventManager $discordEventManager,
        DBAL $db
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

        $instance->systemStat->add(TypeSystemStat::CORE);

        return $instance;
    }

    /**
     * @throws ReflectionException
     * @throws IntentException
     */
    public static function create(Configurator $configurator): static
    {
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

        $symbolCommand = $globalConfig['symbolCommand'] ?? '~';
        $useNewCommandSystem = $globalConfig['useNewCommandSystem'] ?? true;

        if (empty($globalConfig['databaseParams']) || !is_array($globalConfig['databaseParams'])) {
            throw new RuntimeException('db params invalid');
        }

        Config::setDatabaseParams($globalConfig['databaseParams']);
        Config::setSymbolCommand($symbolCommand);
        Config::setUseNewCommandSystem($useNewCommandSystem);

        if ($initDI) {
            new Container();
        }

        Container::getInstance()->setShared(
            'systemStat',
            Container::getInstance()->createObject(SystemStat::class)
        );

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

        $core->coreActivate('default');

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

        $this->systemStat->view();

        ConsoleLogger::showMessage('app run');

        $this->discord->run();
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

    public function getDatabaseAdapter(): DBAL
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

    public function coreActivate(string $guild = 'undefined'): void
    {
        $key = $this->licenseManager->getKey(
            $guild,
            KeyPrefixStorage::MAIN
        );

        if ($key === null) {
            $newKey = $this->licenseManager->getActivationService()
                ->newKey(
                    $guild,
                    KeyPeriod::createDefault(time(), strtotime('+30 days')),
                    master: true
                )
            ;

            $componentInfo = (new ComponentInfo())
                ->setComponentName('core')
                ->setComponentClass(Core::class)
                ->setUseComponentClass(true)
            ;

            $newKey->setComponentInfo($componentInfo);

            $this->licenseManager->activate($newKey, ActivateMethodStorage::MAIN);
        }
    }
}
