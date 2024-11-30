<?php

namespace Discord\Bot\System\Repository\DTO;

use Discord\Bot\Core;
use Discord\Bot\System\Repository\Storage\CriteriaOperatorStorage;

class LikeCriteria extends Criteria
{
    protected string $operator = CriteriaOperatorStorage::LIKE;
}
