<?php

namespace Discord\Bot\Scheduler\Interface;

use Discord\Bot\Scheduler\Storage\ExecuteSchemeStorage;

interface ExecuteSchemeInterface
{
    /**
     * `1 (default): execute task -> set status to "done" automatically`
     *
     * `2 - manual set status to done`
     */
    public const SCHEME = ExecuteSchemeStorage::AUTO;
}
