<?php

namespace Discord\Bot\System\Traits;

use Discord\Bot\System\Discord\DiscordEventManager;
use Discord\Bot\Scheduler\ScheduleManager;
use Discord\Bot\System\ComponentsFacade;
use Discord\Discord;
use Vengine\Libraries\DBAL\Adapter;
use Discord\Bot\System\Events\EventDispatcher;
use Discord\Bot\System\License\LicenseManager;
use Discord\Bot\System\Logger;
use Discord\Bot\System\Migration\MigrationManager;

/**
 * @property EventDispatcher $eventDispatcher
 * @property Logger $logger
 * @property LicenseManager $licenseManager
 * @property ScheduleManager $scheduleManager
 * @property ComponentsFacade $components
 * @property MigrationManager $migrationManager
 * @property DiscordEventManager $discordEventManager
 * @property Adapter $db
 * @property Discord $discord
 */
trait Injectable
{
}