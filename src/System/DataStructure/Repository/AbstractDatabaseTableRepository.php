<?php

namespace Discord\Bot\System\DataStructure\Repository;

use App\System\Repository\AbstractRepository;
use Discord\Bot\System\DataStructure\Entity\AbstractTableEntity;

abstract class AbstractDatabaseTableRepository extends AbstractRepository
{
    public function createEntity(string $dataKey, string $column = ''): ?AbstractTableEntity
    {
        return null;
    }
}
