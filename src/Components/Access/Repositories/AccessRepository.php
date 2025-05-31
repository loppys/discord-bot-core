<?php

namespace Discord\Bot\Components\Access\Repositories;

use Vengine\Libraries\Repository\AbstractRepository;
use Discord\Bot\Components\Access\Entity\AccessEntity;
use Vengine\Libraries\Repository\Entity\AbstractEntity;

/**
 * @method AccessEntity|null createEntity(array $criteria = [])
 * @method AccessEntity|null createEntityByArray(array $data)
 * @method AccessEntity|null newEntity()
 */
class AccessRepository extends AbstractRepository
{
    protected string $table = 'access';

    protected string $primaryKey = 'ac_id';

    protected array $columnMap = [
        'ac_usr_id',
        'ac_srv_id',
        'ac_group_lvl',
    ];

    protected string $entityClass = AccessEntity::class;
}
