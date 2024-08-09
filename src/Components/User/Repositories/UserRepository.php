<?php

namespace Discord\Bot\Components\User\Repositories;

use Discord\Bot\System\Repository\AbstractRepository;
use Discord\Bot\System\Repository\Storage\JoinTypeStorage;

class UserRepository extends AbstractRepository
{
    protected array $dependencyTableList = [
        [
            'fromTable' => 'access',
            'aliasTable' => 'ac',
            'conditionFromColumns' => ['usr_id' => 'ac_usr_id'],
            'joinType' => JoinTypeStorage::LEFT,
        ]
    ];
}
