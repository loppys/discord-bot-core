<?php

namespace Discord\Bot\Components\Stat\Repositories;

use Discord\Bot\System\Repository\AbstractRepository;

class MessagesRepository extends AbstractRepository
{
    protected string $table = '__stat_messages';

    protected string $primaryKey = 'stm_id';

    protected array $columnMap = [
        'stm_st_id',
        'stm_msg_count',
        'stm_bad_msg',
    ];
}
