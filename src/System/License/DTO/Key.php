<?php

namespace Discord\Bot\System\License\DTO;

class Key
{
    protected string $value = '';

    protected KeyPeriod $period;

    protected string $guild = '';

    protected ?ComponentInfo $componentInfo = null;

    // Подходит для всего и при этом вечный
    protected bool $master = false;

    protected bool $universe = false;

    protected bool $trial = false;

    private bool $prefixAdded = false;

    public function addPrefix(string $prefix): static
    {
        if ($this->prefixAdded) {
            return $this;
        }

        $this->value = $prefix . $this->value;

        $this->prefixAdded = true;

        return $this;
    }

    public function setTrial(bool $trial): static
    {
        $this->trial = $trial;

        return $this;
    }

    public function isTrial(): bool
    {
        return $this->trial;
    }

    public function isUniverse(): bool
    {
        return $this->universe;
    }

    public function setUniverse(bool $universe): static
    {
        $this->universe = $universe;

        return $this;
    }

    public function isMaster(): bool
    {
        return $this->master;
    }

    public function setMaster(bool $master): static
    {
        $this->master = $master;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getPeriod(): KeyPeriod
    {
        return $this->period;
    }

    public function setPeriod(KeyPeriod $period): static
    {
        $this->period = $period;

        return $this;
    }

    public function getGuild(): string
    {
        return $this->guild;
    }

    public function setGuild(string $guild): static
    {
        $this->guild = $guild;

        return $this;
    }

    public function getComponentInfo(): ?ComponentInfo
    {
        return $this->componentInfo;
    }

    public function setComponentInfo(?ComponentInfo $componentInfo): static
    {
        $this->componentInfo = $componentInfo;

        return $this;
    }

    public function isExpired(): bool
    {
        return strtotime($this->period->getTo()) < time();
    }
}
