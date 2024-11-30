<?php

namespace Discord\Bot\Scheduler\Parts;

use Discord\Bot\Scheduler\Storage\TaskTypeStorage;

class DefaultTask extends AbstractTask
{
    protected int $type = TaskTypeStorage::DEFAULT;
}
