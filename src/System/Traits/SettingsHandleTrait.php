<?php

namespace Discord\Bot\System\Traits;

use Discord\Bot\Components\Settings\Entity\Setting;
use Discord\Bot\Components\Settings\SettingsComponent;
use Doctrine\DBAL\Exception;

trait SettingsHandleTrait
{
    /**
     * @var array<Setting|array{
     *     stg_guild:string,
     *     stg_name:string,
     *     stg_value:string,
     *     stg_type:string,
     *     stg_enabled:bool,
     *     stg_required:bool,
     *     stg_system:bool,
     *     stg_hidden:bool
     * }>
     */
    protected array $settings = [];

    /**
     * @throws Exception
     */
    private function settingsHandle(): void
    {
        if (!$this->settings) {
            return;
        }

        foreach ($this->settings as $setting) {
            if ($this instanceof SettingsComponent) {
                $this->addSetting($setting);
            } else {
                $this->components->settings->addSetting($setting);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function getSettingValue(string $name, string $guild): ?Setting
    {
        if ($this instanceof SettingsComponent) {
            return $this->getSettingByName($name, $guild);
        }

        if (!$this->components->isCreated('settings')) {
            return null;
        }

        return $this->components->settings->getSettingByName($name, $guild);
    }
}
