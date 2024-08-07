<?php

use Discord\Bot\Scheduler\Storage\TaskTypeStorage;
use Discord\Bot\SystemCheck;

return [
    'system-check' => [
        'handler' => [SystemCheck::class, 'run'],
        'arguments' => null,
        'type' => TaskTypeStorage::PERIODIC,
    ]
];
