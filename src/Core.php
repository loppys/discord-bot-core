<?php

namespace Discord\Bot;

use Discord\Bot\System\Discord\DiscordEventManager;
use Discord\Bot\System\DBAL;
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

class Core implements SingletonInterface
{
    use SingletonTrait;
    use ContainerInjection;

    protected Discord $discord;

    protected LoopInterface $loop;

    public function __construct(
        MigrationManager $migrationManager,
        ScheduleManager $scheduleManager,
        ComponentsFacade $componentFacade,
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
            ->setShared('components', $componentFacade)
            ->setShared('scheduleManager', $scheduleManager)
            ->setShared('migrationManager', $migrationManager)
            ->setShared('discordEventManager', $discordEventManager)
            ->setShared('db', $db)
        ;

        static::$instance = $this;
    }

    /**
     * @throws ReflectionException
     * @throws IntentException
     */
    public static function create(
        string $globalConfigPath = '',
        array $discordOptions = [],
        ?ComponentsFacade $overrideComponentsFacade = null,
        bool $initDI = true
    ): static {
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

        $_SERVER['create.auto'] = true;
        $_SERVER['core.dir'] = __DIR__;

        if (empty($discordOptions)) {
            throw new RuntimeException('discord options empty');
        }

        $discord = new Discord($discordOptions);

        /** @var Core $core */
        $core = Container::getInstance()->createObject(static::class);

        if (!is_object($core)) {
            throw new RuntimeException('Create core fail');
        }

        $core->setDiscord($discord);

        $core->components->initComponents();

        $core->scheduleManager->setLoop(
            $core->getLoop()
        );

        if ($overrideComponentsFacade !== null) {
            $overrideComponentsFacade->overrideClassList(
                $core->components->getClassList()
            );

            $core->getContainer()->setShared('components', $overrideComponentsFacade);
        }

        $core->discordEventManager
            ->initDiscord($discord)
            ->initDefaultEvents()
        ;

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
}
