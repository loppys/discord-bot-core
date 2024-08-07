<?php

namespace Discord\Bot\Scheduler\Interface;

interface QueueTaskInterface
{
    public function getName(): string;

    public function getQueueGroup(): string;

    public function setQueueGroup(string $group): static;
}
