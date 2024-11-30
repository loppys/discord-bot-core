<?php

namespace Discord\Bot\System\Interfaces;

use Discord\Bot\System\Repository\Schema\QueryBuilder;

interface QueryCreatorInterface
{
    public function queryBuilder(): QueryBuilder;

    public function destructBuilder(): void;
}
