<?php

namespace Discord\Bot\Components\Settings\Entity;

use Discord\Bot\Components\Settings\Storages\SettingsTypeStorage;
use Discord\Bot\System\Repository\Entity\AbstractEntity;

/**
 * @property int $stg_id
 * @property string $stg_guild
 * @property string $stg_name
 * @property string $stg_value
 * @property string $stg_type
 * @property bool $stg_enabled
 * @property bool $stg_required
 * @property bool $stg_system
 */
class Setting extends AbstractEntity
{
    public function getStg_value(): string
    {
        return match ($this->stg_type) {
            SettingsTypeStorage::SELECT => @unserialize($stg_value),
            default => $this->stg_value,
        };
    }

    public function setStg_value(string $stg_value): void
    {
        $this->stg_value = match ($this->stg_type) {
            SettingsTypeStorage::BOOL => (bool)$stg_value,
            SettingsTypeStorage::SELECT => serialize($stg_value),
            SettingsTypeStorage::NUMBER => (int)$stg_value,
            default => $stg_value,
        };
    }

    public function setStg_enabled(bool|int $stg_enabled): void
    {
        $this->stg_enabled = (bool)$stg_enabled;
    }

    public function getStg_enabled(): bool
    {
        return $this->stg_enabled;
    }

    public function setStg_required(bool|int $stg_required): void
    {
        $this->stg_required = (bool)$stg_required;
    }

    public function getStg_required(): bool
    {
        return $this->stg_required;
    }

    public function setStg_system(bool|int $stg_system): void
    {
        $this->stg_system = (bool)$stg_system;
    }

    public function getStg_system(): bool
    {
        return $this->stg_system;
    }
}
