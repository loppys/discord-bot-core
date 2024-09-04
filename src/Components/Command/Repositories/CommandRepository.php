<?php

namespace Discord\Bot\Components\Command\Repositories;

use Discord\Bot\System\Repository\AbstractRepository;
use Discord\Bot\Components\Command\Entity\CommandEntity;
use Discord\Bot\System\Repository\Entity\AbstractEntity;
use Discord\Bot\System\Repository\Storage\JoinTypeStorage;

/**
 * @method CommandEntity|null createEntity(array $criteria = [])
 * @method CommandEntity|null createEntityByArray(array $data)
 */
class CommandRepository extends AbstractRepository
{
    protected string $table = 'commands';

    protected array $columnMap = [
        'name',
        'access',
        'scheme',
        'class',
        'description',
    ];

    protected string $entityClass = CommandEntity::class;
}
