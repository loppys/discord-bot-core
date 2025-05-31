<?php

namespace Discord\Bot;

use Discord\Bot\System\ComponentsFacade;
use Discord\Bot\System\Traits\DefaultObjectCreatorTrait;

class Configurator
{
    use DefaultObjectCreatorTrait;

    /**
     * @see check README.md
     */
    protected string $globalConfigPath = '';

    protected array $discordOptions = [];

    protected array $overrideComponents = [];

    protected bool $initDI = true;

    protected array $discordEvents = [];

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

    /**
     * @deprecated
     */
    public function isInitDI(): bool
    {
        return $this->initDI;
    }

    /**
     * @deprecated
     */
    public function setInitDI(bool $initDI): static
    {
        $this->initDI = $initDI;

        return $this;
    }
}
