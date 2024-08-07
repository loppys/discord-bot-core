<?php

namespace Discord\Bot\Scheduler\Storage;

/*
 * first - при получении очереди будет в числе первых
 * last - аналогично first, только в числе последних
 */
class QueueGroupStorage
{
    public const DEFAULT = 'default';

    public const PERIODIC = 'periodic';

    public const FIRST = 'first';

    public const LAST = 'last';

    public const GROUPS = [
        self::DEFAULT,
        self::PERIODIC,
        self::FIRST,
        self::LAST
    ];
}
