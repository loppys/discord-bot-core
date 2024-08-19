<?php

namespace Discord\Bot\Components\Command\DTO;

use Discord\Bot\Config;

class Command
{
    protected string $rawCommand = '';

    protected array $tempParts = [];

    protected string $commandName = '';

    protected array $flags = [];

    protected array $arguments = [];

    public function __construct(string $message)
    {
        $this->rawCommand = $message;
        $this->tempParts = explode(' ', $this->rawCommand);

        $this->parseName()->parseArguments()->parseFlags();
    }

    public function getCommandName(): string
    {
        return $this->commandName;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function replaceName(string $newName): static
    {
        $this->commandName = $newName;

        return $this;
    }

    protected function parseName(): static
    {
        $name = $this->tempParts[0];

        unset($this->tempParts[0]);
        
        $this->commandName = str_replace(Config::getSymbolCommand(), '', $name);

        return $this;
    }

    protected function parseFlags(): void
    {
        foreach ($this->tempParts as $key => $part) {
            if ($part[0] === '-') {
                $part = str_replace('-', '', $part);

                $this->flags[] = $part;

                unset($this->tempParts[$key]);
            }
        }
    }

    protected function parseArguments(): static
    {
        foreach ($this->tempParts as $key => $part) {
            if ($part[0] !== '-') {
                $this->arguments[] = $part;

                unset($this->tempParts[$key]);
            }
        }

        return $this;
    }
}
