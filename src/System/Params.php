<?php

namespace Discord\Bot\System;

use Discord\Bot\System\Traits\DefaultObjectCreatorTrait;

class Params
{
    use DefaultObjectCreatorTrait;

    private array $data = [];

    public function has(string $name): bool
    {
        return !empty($this->data[$name]);
    }

    public function get(string $name, mixed $defaultValue = null): mixed
    {
        return $this->data[$name] ?? $defaultValue ?? null;
    }

    public function set(string $name, mixed $value): static
    {
        $this->data[$name] = $value;

        return $this;
    }
}
