<?php

use Discord\Bot\Scheduler\Storage\TaskTypeStorage;
use Discord\Bot\System\Migration\MigrationManager;
use Discord\Bot\SystemCheck;

return [
    'system-check' => [
        'handler' => [SystemCheck::class, 'run'],
        'arguments' => null,
        'interval' => 900,
        'type' => TaskTypeStorage::PERIODIC,
    ],
    'migration-execute' => [
        'handler' => [MigrationManager::class, 'run'],
        'type' => TaskTypeStorage::PERIODIC,
        'interval' => 3600,
    ],
];
