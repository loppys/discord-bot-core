<?php

namespace Discord\Bot\Scheduler\Interface;

interface TaskExecuteInterface
{
    public function execute(): bool;
}
