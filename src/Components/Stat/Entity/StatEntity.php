<?php

namespace Discord\Bot\Components\Stat\Entity;

use Discord\Bot\System\Repository\Entity\AbstractEntity;

/**
 * @property int $st_id
 * @property int $st_type
 * @property string $st_name
 * @property string $st_value
 * @property string $st_usr_id
 * @property string $st_srv_id
 * @property int $stl_lvl
 * @property float $stl_current_exp
 * @property float $stl_next_exp
 * @property float $stl_multiplier
 * @property int $stm_msg_count
 * @property int $stm_bad_msg
 */
class StatEntity extends AbstractEntity
{
    protected array $otherColumns = [
        'stl_lvl',
        'stl_current_exp',
        'stl_next_exp',
        'stl_multiplier',
        'stm_msg_count',
        'stm_bad_msg',
    ];
}
