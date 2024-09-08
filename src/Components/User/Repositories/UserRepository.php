<?php

namespace Discord\Bot\Components\User\Repositories;

use Discord\Bot\Components\User\Entity\User;
use Discord\Bot\System\Repository\AbstractRepository;

/**
 * @method User|null createEntity(array $criteria = [])
 * @method User|null createEntityByArray(array $data)
 */
class UserRepository extends AbstractRepository
{
    protected string $table = 'users';

    protected string $primaryKey = 'usr_id';

    protected array $columnMap = [
        'usr_hidden',
        'usr_system'
    ];

    protected string $entityClass = User::class;
}
