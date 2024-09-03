<?php

namespace Discord\Bot\Components\User\Repositories;

use Discord\Bot\Components\User\Entity\User;
use Discord\Bot\System\Repository\AbstractRepository;

class UserRepository extends AbstractRepository
{
    protected string $table = 'users';

    protected string $primaryKey = 'usr_id';

    protected array $columnMap = [
        'usr_stat_id',
    ];

    protected string $entityClass = User::class;

    public function createEntity(array $criteria = []): ?User
    {
        return parent::createEntity($criteria);
    }
}
