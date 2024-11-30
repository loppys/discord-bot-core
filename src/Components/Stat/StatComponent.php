<?php

namespace Discord\Bot\Components\Stat;

use Discord\Bot\Components\Stat\Services\StatService;
use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Scheduler\Storage\TaskTypeStorage;

/**
 * @method StatService getService()
 */
class StatComponent extends AbstractComponent
{
    protected array $migrationList = [
        __DIR__ . '/Migrations/'
    ];

    protected array $scheduleTasks = [
        'stat-sync-users' => [
            'handler' => [StatService::class, 'syncUsers'],
            'interval' => 3600,
            'type' => TaskTypeStorage::PERIODIC,
        ]
    ];

    public function __construct(StatService $service)
    {
        parent::__construct($service);
    }
}
