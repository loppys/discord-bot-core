<?php

use Discord\Bot\Scheduler\Storage\TaskTypeStorage;
use Discord\Bot\System\Migration\MigrationManager;
use Discord\Bot\SystemCheck;

return [
    'system-check' => [
        'handler' => [SystemCheck::class, 'run'],
        'arguments' => null,
        'interval' => 360,
        'type' => TaskTypeStorage::PERIODIC,
    ],
    'migration-runtime-collect' => [
        'handler' => [MigrationManager::class, 'runtimeCollectMigrations'],
        'type' => TaskTypeStorage::PERIODIC,
        'interval' => 120,
    ],
    'migration-execute' => [
        'handler' => [MigrationManager::class, 'run'],
        'type' => TaskTypeStorage::PERIODIC,
        'interval' => 240,
    ],
];
