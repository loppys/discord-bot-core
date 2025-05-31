<?php

namespace Discord\Bot\System\GlobalRepository;

use Discord\Bot\System\GlobalRepository\Entities\LogEntity;
use Vengine\Libraries\Repository\AbstractRepository;

/**
 * @method LogEntity|null createEntity(array $criteria = [])
 * @method LogEntity|null createEntityByArray(array $data)
 */
class LogRepository extends AbstractRepository
{
    protected string $table = '_logs';

    protected array $columnMap = [
        'lg_value',
        'lg_source'
    ];

    protected string $entityClass = LogEntity::class;
}
