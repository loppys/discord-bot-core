<?php

namespace Discord\Bot\Components\Access\Repositories;

use Discord\Bot\System\Repository\AbstractRepository;
use Discord\Bot\Components\Access\Entity\AccessEntity;

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

    public function createEntity(array $criteria = []): ?AccessEntity
    {
        return parent::createEntity($criteria);
    }
}
