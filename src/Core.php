<?php

namespace Discord\Bot;

use Doctrine\DBAL\Exception;
use Vengine\Libraries\DBAL\Adapter;
use Vengine\Libraries\Console\ConsoleLogger;
use Discord\Bot\System\Interfaces\ComponentLicenseInterface;
use Discord\Bot\System\License\DTO\ComponentInfo;
use Discord\Bot\System\License\DTO\KeyPeriod;
use Discord\Bot\System\License\LicenseInjection;
use Discord\Bot\System\Traits\ContainerInjection;
use Discord\Bot\System\Traits\SingletonTrait;
use Discord\Bot\Scheduler\ScheduleManager;
use Discord\Bot\System\ComponentsFacade;
use Discord\Bot\System\Interfaces\SingletonInterface;
use Discord\Discord;
use React\EventLoop\LoopInterface;
use RuntimeException;
use Vengine\Libs\DI\interfaces\ContainerAwareInterface;

class Core implements SingletonInterface, ComponentLicenseInterface, ContainerAwareInterface
{
    use SingletonTrait;
    use ContainerInjection;
    use LicenseInjection;

    protected Discord $discord;

    protected LoopInterface $loop;

    public function __construct()
    {
        if (empty($_SERVER['create.auto'])) {
            trigger_error(
                'Manual creation of an object is not recommended, use the create() method.',
                E_USER_WARNING
            );
        }

        if (!empty(static::$instance)) {
            throw new RuntimeException('Creation of a second copy is prohibited. use Core::getInstance()');
        }

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

    public function writeLog(string $value): bool
    {
        return $this->logger->write($value, 'core');
    }

    /**
     * @throws Exception
     */
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

            $this->licenseManager->activate($newKey);
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
