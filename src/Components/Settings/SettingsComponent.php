<?php

namespace Discord\Bot\Components\Settings;

use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\Settings\Entity\Setting;
use Discord\Bot\Components\Settings\Services\SettingsService;
use Discord\Bot\Scheduler\Storage\TaskTypeStorage;
use Doctrine\DBAL\Exception;

/**
 * @method SettingsService getService()
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

    public function __construct(SettingsService $service)
    {
        parent::__construct($service);
    }

    /**
     * @throws Exception
     */
    protected function addSetting(array|Setting $setting): static
    {
        $this->getService()->addSetting($setting);

        return $this;
    }
}