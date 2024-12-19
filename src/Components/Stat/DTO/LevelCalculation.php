<?php

namespace Discord\Bot\Components\Stat\DTO;

use Discord\Bot\System\Traits\DefaultObjectCreatorTrait;

class LevelCalculation
{
    use DefaultObjectCreatorTrait;

    protected int $level = 0;

    protected float $currentExp = 0.0;

    protected float $nextExp = 0.0;

    protected float $multiplier = 0.0;

    protected float $addExp = 0;

    protected ?int $addLevels = null;

    public function getAddLevels(): ?int
    {
        return $this->addLevels;
    }

    public function setAddLevels(?int $addLevels): static
    {
        $this->addLevels = $addLevels;

        return $this;
    }

    public function setAddExp(float $addExp): static
    {
        $this->addExp = $addExp;

        return $this;
    }

    public function getAddExp(): float
    {
        return $this->addExp;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function setCurrentExp(float $currentExp): static
    {
        $this->currentExp = $currentExp;

        return $this;
    }

    public function setMultiplier(float $multiplier): static
    {
        $this->multiplier = $multiplier;

        return $this;
    }

    public function setNextExp(float $nexExp): static
    {
        $this->nextExp = $nexExp;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getCurrentExp(): float
    {
        return $this->currentExp;
    }

    public function getMultiplier(): float
    {
        return $this->multiplier;
    }

    public function getNextExp(): float
    {
        return $this->nextExp;
    }
}
