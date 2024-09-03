<?php

namespace Discord\Bot\Components\Command\DTO;

use Discord\Bot\Components\Command\Entity\CommandEntity;
use Discord\Bot\Components\Command\Storage\CommandMigrationTypeStorage;
use Discord\Bot\Scheduler\Interface\QueueTaskInterface;
use Discord\Bot\Scheduler\Storage\QueueGroupStorage;

class CommandMigration extends CommandEntity implements QueueTaskInterface
{
    protected int $type = CommandMigrationTypeStorage::EMPTY;

    public string $taskName = '';

    public string $queueGroup = QueueGroupStorage::DEFAULT;

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): string
    {
        return $this->taskName;
    }

    public function getQueueGroup(): string
    {
        return $this->queueGroup;
    }

    public function setQueueGroup(string $group): static
    {
        $this->queueGroup = $group;

        return $this;
    }
}
