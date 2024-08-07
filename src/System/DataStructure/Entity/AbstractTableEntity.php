<?php

namespace Discord\Bot\System\DataStructure\Entity;

use App\Repository\Entity\AbstractEntity;

abstract class AbstractTableEntity extends AbstractEntity
{
    protected string $table = '';

    protected array $columns = [];
}
