<?php

namespace Discord\Bot\Components\Access\Storage;

/*
 * Всё что выше DEVELOPER будет считаться root
 */
class BaseAccessStorage
{
    public const GUEST = 0;

    public const USER = 1 << 1;

    public const MODERATOR = 1 << 2;

    public const ADMIN = 1 << 3;

    public const OWNER = 1 << 4;

    public const DEVELOPER = 1 << 6;
}
