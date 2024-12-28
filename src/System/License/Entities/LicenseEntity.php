<?php

namespace Discord\Bot\System\License\Entities;

use Vengine\Libraries\Repository\Entity\AbstractEntity;

/**
 * @property int $lns_id
 * @property string $lns_guild
 * @property bool $lns_universe
 * @property string $lns_key
 * @property bool $lns_expired
 * @property bool $lns_infinity
 * @property bool $lns_master
 * @property bool $lns_trial
 * @property int $lns_time_end
 * @property int $lns_time_activate
 * @property string $lns_component_class
 * @property string $lns_component_name
 * @property bool $lns_use_component_class
 */
class LicenseEntity extends AbstractEntity
{
    public function getLnsTimeEnd(): int
    {
        return strtotime($this->getDataByName('lns_time_end'));
    }

    public function getLnsTimeActivate(): int
    {
        return strtotime($this->getDataByName('lns_time_activate'));
    }
}
