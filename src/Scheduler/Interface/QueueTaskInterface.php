<?php

namespace Discord\Bot\Scheduler\Interface;

interface QueueTaskInterface
{
    public function getName(): string;

    public function getQueueGroup(): string;

    public function setQueueGroup(string $group): static;

    public function addLaunch(): static;

    public function getLaunchesCount(): int;

    public function setMaxLaunches(int $maxLaunches): static;

    public function getMaxLaunches(): int;
}
