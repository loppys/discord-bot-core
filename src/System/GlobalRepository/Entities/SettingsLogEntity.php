<?php

namespace Discord\Bot\System\GlobalRepository\Entities;

use Discord\Bot\System\Repository\Entity\AbstractEntity;

/**
 * @property int $stl_id
 * @property string $stl_before
 * @property string $stl_after
 */
class SettingsLogEntity extends AbstractEntity
{
    public function setStlBefore(array $value): void
    {
        $this->setDataByName('stl_before', json_encode($value));
    }

    public function setStlAfter(array $value): void
    {
        $this->setDataByName('stl_after', json_encode($value));
    }

    public function getStlBefore(): string
    {
        return json_decode($this->getDataByName('stl_before') ?? '', true);
    }

    public function getStlAfter(): string
    {
        return json_decode($this->getDataByName('stl_after') ?? '', true);
    }
}
