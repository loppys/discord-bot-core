<?php

namespace Discord\Bot\System\GlobalRepository\Entities;

use Discord\Bot\System\Repository\Entity\AbstractEntity;

/**
 * @property int $stl_id
 * @property string|array $stl_before
 * @property string|array $stl_after
 */
class SettingsLogEntity extends AbstractEntity
{
    public function setStl_before(array $value): void
    {
        $this->stl_before = json_encode($value);
    }

    public function setStl_after(array $value): void
    {
        $this->stl_after = json_encode($value);
    }

    public function getStl_before(): string
    {
        return json_decode($this->stl_before, true);
    }

    public function getStl_after(): string
    {
        return json_decode($this->stl_after, true);
    }
}
