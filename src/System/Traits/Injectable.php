<?php

namespace Discord\Bot\System\Traits;

use Discord\Bot\Scheduler\ScheduleManager;
use Discord\Bot\System\ComponentsFacade;
use Discord\Bot\System\DBAL;
use Discord\Bot\System\Migration\MigrationManager;

/**
 * @property ScheduleManager $scheduleManager
 * @property ComponentsFacade $components
 * @property MigrationManager $migrationManager
 * @property DBAL $db
 */
trait Injectable
{
}