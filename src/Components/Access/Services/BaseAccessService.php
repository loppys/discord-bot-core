<?php

namespace Discord\Bot\Components\Access\Services;

use Discord\Bot\Components\Access\Storage\BaseAccessStorage;

class BaseAccessService
{
    public function isRoot(int $access): bool
    {
        return $access > BaseAccessStorage::DEVELOPER;
    }
}
