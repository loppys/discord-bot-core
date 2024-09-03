<?php

namespace Discord\Bot\Components\Command\Storage;

use Discord\Bot\Components\Access\Storage\BaseAccessStorage;

class FlagAccessStorage
{
    public const LOW_ACCESS = [
        BaseAccessStorage::GUEST => [],
        BaseAccessStorage::USER => [
            CommandFlagStorage::PRIVATE_MESSAGE,
            CommandFlagStorage::IGNORE_ARGUMENTS,
        ]
    ];

    public const HIGH_ACCESS = [
        BaseAccessStorage::MODERATOR => self::LOW_ACCESS[BaseAccessStorage::USER],
        BaseAccessStorage::ADMIN => [
            CommandFlagStorage::OTHER_USER,
            CommandFlagStorage::PRIVATE_MESSAGE,
            CommandFlagStorage::IGNORE_ARGUMENTS,
        ],
        BaseAccessStorage::OWNER => [
            CommandFlagStorage::OTHER_USER,
            CommandFlagStorage::PRIVATE_MESSAGE,
            CommandFlagStorage::IGNORE_ARGUMENTS,
        ]
    ];

    public const ABSOLUTE_ACCESS = [
        BaseAccessStorage::DEVELOPER => CommandFlagStorage::ALL_FLAGS
    ];
}
