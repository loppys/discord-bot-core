<?php

namespace Discord\Bot\Scheduler\Parts;

use Discord\Bot\Scheduler\Interface\PeriodicTaskInterface;
use Discord\Bot\Scheduler\Storage\TaskTypeStorage;

class PeriodicTask extends AbstractTask implements PeriodicTaskInterface
{
    protected int $type = TaskTypeStorage::PERIODIC;

    // В секундах
    protected int $periodicInterval = 60;

    public function getPeriodicInterval(): int
    {
        return $this->periodicInterval;
    }

    public function setPeriodicInterval(int $periodicInterval): static
    {
        $this->periodicInterval = $periodicInterval;

        return $this;
    }
}
