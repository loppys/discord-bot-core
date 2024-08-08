<?php

namespace Discord\Bot\System\DataStructure\Entity;

use Discord\Bot\System\Repository\Entity\AbstractEntity;

abstract class AbstractTableEntity extends AbstractEntity
{
    protected string $table = '';

    protected array $columns = [];
}
