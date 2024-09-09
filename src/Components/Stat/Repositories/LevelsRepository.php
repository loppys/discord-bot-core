<?php

namespace Discord\Bot\Components\Stat\Repositories;

use Discord\Bot\System\Repository\AbstractRepository;

class LevelsRepository extends AbstractRepository
{
    protected string $table = '__stat_level';

    protected string $primaryKey = 'stl_id';

    protected array $columnMap = [
        'stl_st_id',
        'stl_lvl',
        'stl_current_exp',
        'stl_next_exp',
        'stl_multiplier'
    ];
}