<?php

namespace Discord\Bot\Components\Command\DTO;

use Bot\App\Commands\HelpCommand;
use Discord\Bot\Components\Access\Storage\BaseAccessStorage;
use Discord\Bot\Components\Command\Entity\CommandEntity;
use Discord\Bot\Components\Command\Storage\CommandMigrationTypeStorage;
use Discord\Bot\Scheduler\Interface\QueueTaskInterface;
use Discord\Bot\Scheduler\Storage\QueueGroupStorage;

class CommandMigration extends CommandEntity implements QueueTaskInterface
{
    public $name = '';

    public $access = BaseAccessStorage::USER;

    public $class = '';

    public $description = '';

    protected int $type = CommandMigrationTypeStorage::EMPTY;

    protected string $taskName = '';

    protected string $queueGroup = QueueGroupStorage::DEFAULT;

    public function __construct(array $entityData = [])
    {
        $entityData['name'] = $this->name;
        $entityData['access'] = $this->access;
        $entityData['class'] = $this->class;
        $entityData['description'] = $this->description;

        parent::__construct($entityData);
    }

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

    public function addLaunch(): static
    {
        return $this;
    }

    public function getLaunchesCount(): int
    {
        return 1;
    }

    public function setMaxLaunches(int $maxLaunches): static
    {
        return $this;
    }

    public function getMaxLaunches(): int
    {
        return 3;
    }
}
