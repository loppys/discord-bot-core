<?php

namespace App\Repository\Entity;

use ArrayAccess;
use Iterator;

abstract class AbstractEntity implements ArrayAccess, Iterator
{
    protected array $entityData = [];

    private int $iterationKey = 0;

    public function __construct(array $entityData = [])
    {
        $this->entityData = $entityData;
    }

    public function setEntityData(array $entityData): static
    {
        $this->entityData = $entityData;

        $this->rewind();

        return $this;
    }

    public function getEntityData(): array
    {
        return $this->entityData;
    }

    public function offsetExists(mixed $offset): bool
    {
        return !empty($this->entityData[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->entityData[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->entityData[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->entityData[$offset]);
    }

    public function current(): mixed
    {
        return $this->entityData[$this->iterationKey];
    }

    public function next(): void
    {
        ++$this->iterationKey;
    }

    public function key(): int
    {
        return $this->iterationKey;
    }

    public function valid(): bool
    {
        return isset($this->entityData[$this->iterationKey]);
    }

    public function rewind(): void
    {
        $this->iterationKey = 0;
    }
}
