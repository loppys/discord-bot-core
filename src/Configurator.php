<?php

namespace Discord\Bot;

use Discord\Bot\System\ComponentsFacade;

class Configurator
{
    /**
     * 'databaseParams' => [
     * 'dbType' => 'pdo_mysql',
     * 'dbHost' => 'localhost',
     * 'dbName' => 'discord_bot',
     * 'dbLogin' => 'db_user',
     * 'dbPassword' => '****'
     * ]
     */
    protected string $globalConfigPath = '';

    protected array $discordOptions = [];

    protected array $overrideComponents = [];

    protected bool $initDI = true;

    protected array $discordEvents = [];

    public static function create(array $data = []): static
    {
        $obj = new static();

        foreach ($data as $property => $value) {
            $method = 'set' . ucfirst($property);
            if (method_exists($obj, $method)) {
                $obj->{$method}($value);
            }
        }

        return $obj;
    }

    public function getDiscordEvents(): array
    {
        return $this->discordEvents;
    }

    public function setDiscordEvents(array $discordEvents): static
    {
        $this->discordEvents = $discordEvents;

        return $this;
    }

    public function addDiscordEvent(string $name, string $class): static
    {
        if (!empty($this->discordEvents[$name])) {
            return $this;
        }

        $this->discordEvents[$name] = $class;

        return $this;
    }

    public function getGlobalConfigPath(): string
    {
        return $this->globalConfigPath;
    }

    public function setGlobalConfigPath(string $globalConfigPath): static
    {
        $this->globalConfigPath = $globalConfigPath;

        return $this;
    }

    public function getDiscordOptions(): array
    {
        return $this->discordOptions;
    }

    public function setDiscordOptions(array $discordOptions): static
    {
        $this->discordOptions = $discordOptions;

        return $this;
    }

    public function getOverrideComponents(): array
    {
        return $this->overrideComponents;
    }

    public function setOverrideComponents(array $overrideComponents): static
    {
        $this->overrideComponents = $overrideComponents;

        return $this;
    }

    public function isInitDI(): bool
    {
        return $this->initDI;
    }

    public function setInitDI(bool $initDI): static
    {
        $this->initDI = $initDI;

        return $this;
    }
}
