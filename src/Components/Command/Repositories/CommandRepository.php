<?php

namespace Discord\Bot\Components\Command\Repositories;

use Discord\Bot\System\Repository\AbstractRepository;
use Discord\Bot\Components\Command\Entity\Command;

class CommandRepository extends AbstractRepository
{
    public function createEntity(string $dataKey, string $column = ''): Command
    {
        return parent::createEntity($dataKey, $column);
    }
}
