<?php

namespace Discord\Bot\Components\Settings;

use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\Settings\Entity\Setting;
use Discord\Bot\Components\Settings\Events\SettingEventListener;
use Discord\Bot\Components\Settings\Services\SettingsService;
use Discord\Bot\Scheduler\Storage\TaskTypeStorage;
use Doctrine\DBAL\Exception;

/**
 * @method SettingsService getService()
 * @method bool updateSetting(string $name, string $guild, array $data = [])
 */
class SettingsComponent extends AbstractComponent
{
    protected array $migrationList = [
        __DIR__ . '/Migrations/'
    ];

    protected array $scheduleTasks = [
        'create-default-settings' => [
            'handler' => [SettingsService::class, 'createDefaultSettings'],
            'interval' => 900,
            'type' => TaskTypeStorage::PERIODIC,
        ],
    ];

    protected array $_events = [
        SettingEventListener::class => [
            'before.updateSetting',
            'after.updateSetting'
        ],
    ];

    public function __construct(SettingsService $service)
    {
        parent::__construct($service);
    }

    /**
     * @throws Exception
     */
    public function addSetting(array|Setting $setting): static
    {
        $this->getService()->addSetting($setting);

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function updateSettingEventable(string $name, string $guild, array $data = []): bool
    {
        return $this->getService()->updateSetting($name, $guild, $data);
    }
}