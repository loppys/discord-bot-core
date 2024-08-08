<?php

namespace Discord\Bot\System;

use Discord\Bot\Components\Access\AccessComponent;
use Discord\Bot\Components\Command\CommandComponent;
use Discord\Bot\Components\Event\EventComponent;
use Discord\Bot\Components\Interaction\InteractionComponent;
use Discord\Bot\Components\License\LicenseComponent;
use Discord\Bot\Components\Management\ManagementComponent;
use Discord\Bot\Components\Settings\SettingsComponent;
use Discord\Bot\Components\Stat\StatComponent;
use Discord\Bot\Components\User\UserComponent;
use Discord\Bot\Components\Voice\VoiceComponent;
use Discord\Bot\System\Interfaces\ComponentInterface;
use Discord\Bot\System\Facade\AbstractFacade;
use ReflectionException;
use ReflectionClass;
use RuntimeException;

/**
 * @property AccessComponent $access
 * @property CommandComponent $command;
 * @property EventComponent $event;
 * @property InteractionComponent $interactionComponent;
 * @property LicenseComponent $license;
 * @property ManagementComponent $management;
 * @property SettingsComponent $settings;
 * @property StatComponent $stat;
 * @property UserComponent $user;
 * @property VoiceComponent $voice;
 */
class ComponentsFacade extends AbstractFacade
{
    /**
     * @var array<ComponentInterface>
     */
    protected array $initClassList = [
        'access' => AccessComponent::class,
        'command' => CommandComponent::class,
        'event' => EventComponent::class,
        'interactionComponent' => InteractionComponent::class,
        'license' => LicenseComponent::class,
        'management' => ManagementComponent::class,
        'settings' => SettingsComponent::class,
        'stat' => StatComponent::class,
        'user' => UserComponent::class,
        'voice' => VoiceComponent::class,
    ];

    /**
     * @throws ReflectionException
     */
    public function __construct()
    {
        foreach ($this->initClassList as $class) {
            $this->checkComponent($class);
        }

        parent::__construct(false);
    }

    public function getComponentList(): array
    {
        return $this->getClassList();
    }

    /**
     * @throws ReflectionException
     */
    public function initComponents(): static
    {
        foreach ($this->initClassList as $name => $component) {
            $this->add($name, $component);
        }

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function overrideClassList(array $classList): static
    {
        foreach ($classList as $class) {
            $this->checkComponent($class);
        }

        return parent::overrideClassList($classList);
    }

    /**
     * @throws ReflectionException
     */
    public function overrideClass(string $name, string $class): static
    {
        $this->checkComponent($class);

        return parent::overrideClass($name, $class);
    }

    /**
     * @throws ReflectionException
     */
    public function add(string $name, object|string $value): static
    {
        $this->checkComponent($value);

        return parent::add($name, $value);
    }

    /**
     * @throws ReflectionException
     */
    protected function checkComponent(string|object $class): void
    {
        $this->validateComponent($class);
    }

    /**
     * @throws ReflectionException
     */
    protected function validateComponent(string|object $class): bool
    {
        $reflection = new ReflectionClass($class);

        if (!in_array(ComponentInterface::class, $reflection->getInterfaceNames(), true)) {
            throw new RuntimeException("the {$class} must inherit from ComponentInterface");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return false;
        }

        $params = $constructor->getParameters();

        // проверки из-за DI, т.к. нужно точно знать что создавать
        if (!$params[0]->hasType() || !$params[1]->hasType()) {
            return false;
        }

        if ($params[0]->getType() === null || $params[1]->getType() === null) {
            return false;
        }

        return $params[0]->getType()->getName() !== 'mixed' && $params[1]->getType()?->getName() !== 'mixed';
    }
}
