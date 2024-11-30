<?php

namespace Discord\Bot\System\License\DTO;

use Discord\Bot\Components\AbstractComponent;

class ComponentInfo
{
    // Если класс наследуется, то для наследуемых классов тоже будет распространяться ключ
    protected string $componentClass = AbstractComponent::class;

    protected string $componentName = '';

    protected bool $useComponentClass = false;

    public function getComponentClass(): string
    {
        return $this->componentClass;
    }

    public function setComponentClass(string $componentClass): static
    {
        $this->componentClass = $componentClass;

        return $this;
    }

    public function getComponentName(): string
    {
        return $this->componentName;
    }

    public function setComponentName(string $componentName): static
    {
        $this->componentName = $componentName;

        return $this;
    }

    public function isUseComponentClass(): bool
    {
        return $this->useComponentClass;
    }

    public function setUseComponentClass(bool $useComponentClass): static
    {
        $this->useComponentClass = $useComponentClass;

        return $this;
    }
}
