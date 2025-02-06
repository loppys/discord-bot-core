<?php

namespace Discord\Bot\Components\Settings\Services;

use Discord\Bot\Components\Settings\Entity\Setting;
use Discord\Bot\Components\Settings\Repositories\SettingsRepository;
use Discord\Bot\Components\Settings\Storages\DefaultSettingStorage;
use Discord\Bot\Components\Settings\Storages\SettingsTypeStorage;
use Discord\Bot\Core;
use Discord\Parts\Guild\Guild;
use Doctrine\DBAL\Exception;

class SettingsService
{
    protected SettingsRepository $settingsRepository;

    public function __construct(SettingsRepository $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @throws Exception
     */
    public function updateSetting(string $name, string $guild, array $data = []): bool
    {
        return $this->settingsRepository->update(
            $data,
            [
                'stg_guild' => $guild,
                'stg_name' => $name
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function addSetting(array|Setting $setting): static
    {
        if ($this->hasSetting($setting->stg_guild, $setting->stg_name)) {
            return $this;
        }

        if (is_array($setting)) {
            $setting = $this->settingsRepository->createEntityByArray($setting);
        }

        $this->settingsRepository->saveByEntity($setting);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function removeSetting(string $guild, string $name): bool
    {
        if ($this->hasSetting($guild, $name)) {
            return false;
        }

        $setting = $this->getSettingByName($name, $guild);

        if ($setting === null) {
            return false;
        }

        if ($setting->getStgValue()) {
            return false;
        }

        if (!$this->settingsRepository->delete(['stg_guild' => $guild, 'stg_name' => $name])) {
            return false;
        }

        return true;
    }

    /**
     * @return Setting[]
     * @throws Exception
     */
    public function getSystemSettings(string $guild): array
    {
        return $this->settingsRepository->getAll(['stg_guild' => $guild, 'stg_system' => 1]);
    }

    /**
     * @return Setting[]
     * @throws Exception
     */
    public function getEnabledSettings(string $guild): array
    {
        return $this->settingsRepository->getAll(['stg_guild' => $guild, 'stg_enabled' => 1]);
    }

    /**
     * @return Setting[]
     * @throws Exception
     */
    public function getGuildSettings(string $guild): array
    {
        return $this->settingsRepository->getAll(['stg_guild' => $guild, 'stg_hidden' => false]);
    }

    /**
     * @throws Exception
     */
    public function getSettingByName(string $name, string $guild): ?Setting
    {
        return $this->settingsRepository->createEntity([
            'stg_name' => $name,
            'stg_guild' => $guild
        ]);
    }

    /**
     * @throws Exception
     */
    public function hasSetting(string $guild, string $name): bool
    {
        return $this->settingsRepository->has([
            'stg_guild' => $guild,
            'stg_name' => $name
        ]);
    }

    /**
     * @throws Exception
     */
    public function createDefaultSettings(): bool
    {
        /** @var Guild $guild */
        foreach ((array)Core::getInstance()->getDiscord()->guilds->toArray() as $guild) {
            $this->addDefaultSettings($guild->id);
        }

        return true;
    }

    /**
     * @throws Exception
     */
    protected function addDefaultSettings(string $guild): void
    {
        foreach (DefaultSettingStorage::SETTING_MAP as $name => $map) {
            if ($this->hasSetting($guild, $name)) {
                continue;
            }

            if (!empty($map['stg_value']) && is_array($map['stg_value'])) {
                $map['stg_value'] = json_encode($map['stg_value']);
            }

            if (!empty($map['stg_value']) && is_bool($map['stg_value'])) {
                $map['stg_value'] = (int)$map['stg_value'];
            }

            $entity = $this->settingsRepository->createEntityByArray($map);

            if ($entity === null) {
                continue;
            }

            $entity->stg_name = $name;
            $entity->stg_guild = $guild;

            $this->addSetting($entity);
        }
    }
}
