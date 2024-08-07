<?php

namespace Discord\Bot\Scheduler;

use Discord\Bot\Scheduler\Interface\QueueManagerInterface;
use Discord\Bot\Scheduler\Interface\QueueTaskInterface;
use Discord\Bot\Scheduler\Storage\QueueGroupStorage;

class QueueManager implements QueueManagerInterface
{
    protected array $queue = [];

    protected array $tasks = [];

    public function __construct()
    {
        foreach (QueueGroupStorage::GROUPS as $group) {
            $this->queue[$group] = [];
        }
    }

    public function addTask(QueueTaskInterface $queueTask): static
    {
        if ($this->hasTask($queueTask->getName())) {
            return $this;
        }

        $this->tasks[$queueTask->getName()] = $queueTask->getQueueGroup();
        $this->queue[$queueTask->getQueueGroup()][$queueTask->getName()] = $queueTask;

        return $this;
    }

    public function replaceTask(QueueTaskInterface $queueTask): bool
    {
        if (!$this->hasTask($queueTask->getName())) {
            return false;
        }

        if ($this->removeTaskByName($queueTask->getName())) {
            return false;
        }

        $this->addTask($queueTask);

        return true;
    }

    public function getTask(string $name): ?QueueTaskInterface
    {
        $taskGroup = $this->getTaskGroup($name);

        if (empty($taskGroup)) {
            return null;
        }

        return $this->queue[$taskGroup][$name] ?? null;
    }

    public function getTaskGroup(string $name): ?string
    {
        return $this->tasks[$name] ?? null;
    }

    public function removeTaskByName(string $name): bool
    {
        if (!$this->hasTask($name)) {
            return false;
        }

        $taskGroup = $this->getTaskGroup($name);

        if ($taskGroup === null) {
            return false;
        }

        if (empty($this->queue[$taskGroup][$name])) {
            return false;
        }

        unset($this->tasks[$name], $this->queue[$taskGroup][$name]);

        return true;
    }

    public function hasTask(string $name): bool
    {
        return !empty($this->tasks[$name]);
    }

    public function resetQueue(bool $resetPeriodicTask = false): void
    {
        foreach (QueueGroupStorage::GROUPS as $group) {
            if (!$resetPeriodicTask && $group === QueueGroupStorage::PERIODIC) {
                continue;
            }

            $this->queue[$group] = [];
        }
    }

    /**
     * @inheritDoc
     */
    public function compareQueue(bool $resetPeriodicTask = false): array
    {
        $result = [];

        foreach ($this->queue[QueueGroupStorage::FIRST] as $fist) {
            $result[] = $fist;
        }

        foreach ($this->queue[QueueGroupStorage::DEFAULT] as $default) {
            $result[] = $default;
        }

        foreach ($this->queue[QueueGroupStorage::PERIODIC] as $periodic) {
            $result[] = $periodic;
        }

        foreach ($this->queue[QueueGroupStorage::LAST] as $last) {
            $result[] = $last;
        }

        $this->resetQueue($resetPeriodicTask);

        return $result;
    }

    public function getQueue(): array
    {
        return $this->queue;
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function countTasks(): int
    {
        return count($this->tasks);
    }
}
