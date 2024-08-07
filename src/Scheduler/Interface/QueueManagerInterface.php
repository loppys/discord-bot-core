<?php

namespace Discord\Bot\Scheduler\Interface;

interface QueueManagerInterface
{
    public function addTask(QueueTaskInterface $queueTask): static;

    public function replaceTask(QueueTaskInterface $queueTask): bool;

    public function getTask(string $name): QueueTaskInterface|null;

    public function getTaskGroup(string $name): null|string;

    public function removeTaskByName(string $name): bool;

    public function hasTask(string $name): bool;

    public function resetQueue(bool $resetPeriodicTask = false): void;

    /**
     * @param bool $resetPeriodicTask
     * @return array<QueueTaskInterface>
     */
    public function compareQueue(bool $resetPeriodicTask = false): array;

    public function getQueue(): array;

    public function getTasks(): array;

    public function countTasks(): int;
}
