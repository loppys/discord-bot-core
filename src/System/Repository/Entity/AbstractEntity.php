<?php

namespace Discord\Bot\System\Repository\Entity;

use ArrayAccess;
use Iterator;

abstract class AbstractEntity implements ArrayAccess, Iterator
{
    protected array $columns = [];

    protected array $entityData = [];

    private int $iterationKey = 0;

    public function __construct(array $entityData = [])
    {
        $this->entityData = $entityData;
    }

    public function setEntityData(array $entityData): static
    {
        foreach ($entityData as $column => $data) {
            if (!in_array($column, $this->columns, true)) {
                continue;
            }

            $this->entityData[$column] = $data;
        }

        $this->rewind();

        return $this;
    }

    public function setColumns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        if (!empty($this->entityData[$name])) {
            return $this->entityData[$name];
        }

        return null;
    }

    public function __set(string $name, $value): void
    {
        $this->entityData[$name] = $value;

        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
    }

    public function __isset(string $name): bool
    {
        return property_exists($this, $name) || !empty($this->entityData[$name]);
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
