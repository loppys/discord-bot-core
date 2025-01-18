<?php

namespace Discord\Bot\System\Remote;

use Discord\Bot\System\Remote\Interfaces\ClientInterface;

abstract class AbstractClient implements ClientInterface
{
    protected bool $isConnected = false;

    public function connect(): bool
    {
        $this->isConnected = true;
        return $this->isConnected;
    }

    public function disconnect(): void
    {
        $this->isConnected = false;
    }
}
