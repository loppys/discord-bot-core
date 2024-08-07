<?php

namespace Discord\Bot\System\Interfaces;

use Discord\Bot\Scheduler\Parts\AbstractTask;

interface ComponentInfoInterface
{
    /**
     * @return array<AbstractTask>
     */
    public function getScheduleTasks(): array;

    public function getMigrationList(): array;
}
