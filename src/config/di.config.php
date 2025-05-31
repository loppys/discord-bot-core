<?php

use Discord\Bot\System\ComponentsFacade;
use Discord\Bot\System\Discord\DiscordEventManager;
use Discord\Bot\System\Events\EventDispatcher;
use Discord\Bot\System\License\LicenseManager;
use Discord\Bot\System\Logger;
use Discord\Bot\System\Migration\MigrationManager;
use Vengine\Libraries\DBAL\Adapter;
use Discord\Bot\Scheduler\ScheduleManager;
use Discord\Bot\Core;

return [
    'services' => [
        'db.adapter' => [
            'sharedTags' => [
                'db',
                Adapter::class,
            ],
            'shared' => true,
            'closure' => static function () {
                return new Adapter();
            },
        ],
        'event.dispatcher' => [
            'sharedTags' => [
                'eventDispatcher'
            ],
            'class' => EventDispatcher::class,
        ],
        'base.logger' => [
            'sharedTags' => [
                'logger',
                Logger::class,
            ],
            'class' => Logger::class
        ],
        'base.license' => [
            'class' => LicenseManager::class,
            'sharedTags' => [
                'licenseManager'
            ],
        ],
        'DiscordEventManager' => [
            'sharedTags' => [
                'discordEventManager'
            ],
            'class' => DiscordEventManager::class,
        ],
        'scheduler' => [
            'sharedTags' => [
                'scheduleManager',
                'scheduler',
            ],
            'class' => ScheduleManager::class,
        ],
        'components' => [
            'sharedTags' => [
                'componentsFacade',
            ],
            'class' => ComponentsFacade::class
        ],
        'migrations' => [
            'sharedTags' => [
                'migrations',
                'migrationManager',
            ],
            'class' => MigrationManager::class,
        ],
        'bot.core' => [
            'sharedTags' => [
                'core',
            ],
            'class' => Core::class,
            'calls' => [
                'setDiscord' => [
                    'discord' => '@discord',
                ],
            ],
        ],
    ],
];