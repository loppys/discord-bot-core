<?php

namespace Discord\Bot\Components\Command\Storage;

class CommandFlagStorage
{
    public const USE_CASCADE = 'uc';

    public const CASCADE_IGNORE = 'ci';

    public const OTHER_USER = 'ou';

    public const PRIVATE_MESSAGE = 'pm';

    public const IGNORE_ARGUMENTS = 'ia';

    public const ALL_FLAGS = [
        self::OTHER_USER,
        self::PRIVATE_MESSAGE,
        self::USE_CASCADE,
        self::CASCADE_IGNORE,
        self::IGNORE_ARGUMENTS,
    ];
}
