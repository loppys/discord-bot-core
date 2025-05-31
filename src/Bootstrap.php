<?php

namespace Discord\Bot;

use Doctrine\DBAL\Exception;
use Psr\Container\ContainerExceptionInterface;
use Vengine\Libraries\DBAL\DTO\Config as DatabaseConfig;
use Vengine\Libs\DI\config\OverwriteConfigure;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libraries\Console\ConsoleLogger;
use Discord\Exceptions\IntentException;
use Vengine\Libs\DI\Container;
use Discord\Bot\System\Params;
use Discord\Discord;
use RuntimeException;
use ReflectionException;
use Vengine\Libs\DI\Exceptions\NotFoundException;

class Bootstrap
{
    protected static bool $initFlag = false;

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws IntentException
     * @throws Exception
     */
    public static function init(Configurator $configurator): Core
    {
        if (self::$initFlag) {
            return Core::getInstance();
        }

        self::$initFlag = true;

        ConsoleLogger::showMessage('app init');
        return static::createCore($configurator);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     * @throws IntentException
     * @throws Exception
     */
    public static function createCore(Configurator $configurator): Core
    {
        if (Core::hasInstance()) {
            return Core::getInstance();
        }

        ConsoleLogger::showMessage('start core create');

        $_SERVER['create.auto'] = true;
        $_SERVER['core.dir'] = __DIR__;

        $globalConfigPath = $configurator->getGlobalConfigPath();
        $discordOptions = $configurator->getDiscordOptions();
        $overrideComponents = $configurator->getOverrideComponents();

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

        $configure = new OverwriteConfigure();
        $configure->setOverwriteConfigPath(__DIR__ . '/config/di.config.php');

        ConsoleLogger::showMessage('create di');
        $di = new Container($configure);

        if (empty($discordOptions)) {
            throw new RuntimeException('discord options empty');
        }

        ConsoleLogger::showMessage('Discord share');
        $di->addRawService('discord', [
            'sharedTags' => [
                Discord::class,
            ],
            'closure' => static function () use ($discordOptions): Discord {
                return new Discord($discordOptions);
            }
        ]);

        ConsoleLogger::showMessage('create core object');
        /** @var Core $core */
        $core = $di->get('bot.core');

        ConsoleLogger::showMessage('core created');

        if (!is_object($core)) {
            throw new RuntimeException('Create core fail');
        }

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

        $core->discordEventManager->initDefaultEvents();

        if (!empty($configurator->getDiscordEvents())) {
            foreach ($configurator->getDiscordEvents() as $name => $class) {
                $core->discordEventManager->registerDiscordEvent($name, $class);
            }
        }

        $core->baseActivateComponent();

        ConsoleLogger::showMessage('core init success');

        return $core;
    }
}
