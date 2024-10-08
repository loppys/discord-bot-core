<?php

namespace Discord\Bot\System\Traits;

use Discord\Bot\System\Discord\DiscordEventManager;
use Discord\Bot\Scheduler\ScheduleManager;
use Discord\Bot\System\ComponentsFacade;
use Discord\Bot\System\DBAL;
use Discord\Bot\System\Events\EventDispatcher;
use Discord\Bot\System\Migration\MigrationManager;

/**
 * @property EventDispatcher $eventDispatcher
 * @property ScheduleManager $scheduleManager
 * @property ComponentsFacade $components
 * @property MigrationManager $migrationManager
 * @property DiscordEventManager $discordEventManager
 * @property DBAL $db
 */
trait Injectable
{
}