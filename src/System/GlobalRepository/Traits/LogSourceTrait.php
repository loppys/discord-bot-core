<?php

namespace Discord\Bot\System\GlobalRepository\Traits;

trait LogSourceTrait
{
    protected string $source = 'default';

    public function getLogSource(): string
    {
        return $this->source;
    }

    public function setLogSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }
}
