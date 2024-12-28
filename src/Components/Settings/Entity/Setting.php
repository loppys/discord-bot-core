<?php

namespace Discord\Bot\Components\Settings\Entity;

use Discord\Bot\Components\Settings\Storages\SettingsTypeStorage;
use Vengine\Libraries\Repository\Entity\AbstractEntity;

/**
 * @property int $stg_id
 * @property string $stg_guild
 * @property string $stg_name
 * @property string $stg_value
 * @property string $stg_type
 * @property bool $stg_enabled
 * @property bool $stg_required
 * @property bool $stg_system
 * @property bool $stg_hidden
 */
class Setting extends AbstractEntity
{
    public function getStgValue(): string
    {
        return match ($this->getDataByName('stg_type')) {
            SettingsTypeStorage::SELECT => @unserialize($this->getDataByName('stg_value')),
            default => $this->getDataByName('stg_value'),
        };
    }

    public function setStgValue(string $stg_value): void
    {
        $data = match ($this->getDataByName('stg_type')) {
            SettingsTypeStorage::BOOL => (bool)$stg_value,
            SettingsTypeStorage::SELECT => serialize($stg_value),
            SettingsTypeStorage::NUMBER => (int)$stg_value,
            default => $stg_value,
        };

        $this->setDataByName('stg_value', $data);
    }

    public function setStgEnabled(bool|int $stg_enabled): void
    {
        $this->setDataByName('stg_enabled', (bool)$stg_enabled);
    }

    public function getStgEnabled(): bool
    {
        return $this->getDataByName('stg_enabled');
    }

    public function setStgRequired(bool|int $stg_required): void
    {
        $this->setDataByName('stg_required', (bool)$stg_required);
    }

    public function getStgRequired(): bool
    {
        return $this->getDataByName('stg_required');
    }

    public function setStgSystem(bool|int $stg_system): void
    {
        $this->setDataByName('stg_system', (bool)$stg_system);
    }

    public function getStgSystem(): bool
    {
        return $this->getDataByName('stg_system');
    }
}
