<?php

namespace Discord\Bot\Scheduler\Parts;

use Discord\Bot\Scheduler\Interface\DefaultTaskInterface;
use Discord\Bot\Scheduler\Storage\TaskTypeStorage;

class DefaultTask extends AbstractTask implements DefaultTaskInterface
{
    protected int $type = TaskTypeStorage::DEFAULT;
}
