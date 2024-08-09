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
        'ac_group_lvl'
    ];

    protected string $entityClass = AccessEntity::class;

    public function createEntity(string $dataKey, string $column = ''): ?AccessEntity
    {
        return parent::createEntity($dataKey, $column);
    }
}
