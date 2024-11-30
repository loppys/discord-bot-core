<?php

namespace Discord\Bot\Components\Settings\Storages;

class DefaultSettingStorage
{
    public const COMMANDS = 'commands';

    public const EVENTS = 'events';

    public const DEFAULT_ROLE = 'defaultRole';

    public const MANAGEMENT = 'management';

    public const ANALISE = 'analise';

    public const USE_LICENSE = 'useLicense';

    public const SETTING_MAP = [
        self::COMMANDS => [
            'stg_type' => SettingsTypeStorage::BOOL,
            'stg_value' => true,
            'stg_system' => true,
            'stg_required' => true,
        ],
        self::EVENTS => [
            'stg_type' => SettingsTypeStorage::BOOL,
            'stg_value' => true,
            'stg_system' => true,
            'stg_required' => true,
        ],
        self::DEFAULT_ROLE => [
            'stg_type' => SettingsTypeStorage::TEXT,
            'stg_value' => '',
            'stg_system' => true,
        ],
        self::MANAGEMENT => [
            'stg_type' => SettingsTypeStorage::SELECT,
            'stg_value' => [
                'text' => '',
                'images' => '',
                'links' => ''
            ],
            'stg_system' => true,
        ],
        self::ANALISE => [
            'stg_type' => SettingsTypeStorage::BOOL,
            'stg_value' => true,
            'stg_system' => true,
        ],
        self::USE_LICENSE => [
            'stg_type' => SettingsTypeStorage::BOOL,
            'stg_value' => false,
            'stg_system' => true,
            'stg_required' => true,
            'stg_hidden' => true,
        ],
    ];
}