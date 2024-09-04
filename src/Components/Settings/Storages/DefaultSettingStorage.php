<?php

namespace Discord\Bot\Components\Settings\Storages;

class DefaultSettingStorage
{
    public const COMMANDS = 'commands';

    public const EVENTS = 'events';

    public const DEFAULT_ROLE = 'defaultRole';

    public const MANAGEMENT = 'management';

    public const ANALISE = 'analise';

    public const SETTING_MAP = [
        self::COMMANDS => [
            'stg_value' => true,
            'stg_type' => SettingsTypeStorage::BOOL,
            'stg_system' => 1
        ],
        self::EVENTS => [
            'stg_value' => true,
            'stg_type' => SettingsTypeStorage::BOOL,
            'stg_system' => 1
        ],
        self::DEFAULT_ROLE => [
            'stg_value' => '',
            'stg_type' => SettingsTypeStorage::TEXT,
            'stg_system' => 1
        ],
        self::MANAGEMENT => [
            'stg_value' => [
                'text' => '',
                'images' => '',
                'links' => ''
            ],
            'stg_type' => SettingsTypeStorage::SELECT,
            'stg_system' => 1
        ],
        self::ANALISE => [
            'stg_value' => true,
            'stg_type' => SettingsTypeStorage::BOOL,
            'stg_system' => 1
        ],
    ];
}