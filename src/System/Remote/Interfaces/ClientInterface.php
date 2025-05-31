<?php

namespace Discord\Bot\System\Remote\Interfaces;

interface ClientInterface
{
    public function connect(): bool;
    public function execute(string $command): mixed;
    public function disconnect(): void;
}
