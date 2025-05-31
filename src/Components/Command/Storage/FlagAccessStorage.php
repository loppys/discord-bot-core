<?php

namespace Discord\Bot\Components\Command\Storage;

use Discord\Bot\Components\Access\Storage\BaseAccessStorage;

class FlagAccessStorage
{
    public const ACCESS_LIST = [
        BaseAccessStorage::GUEST => [],
        BaseAccessStorage::USER => [
            CommandFlagStorage::PRIVATE_MESSAGE,
            CommandFlagStorage::IGNORE_ARGUMENTS,
        ],
        BaseAccessStorage::MODERATOR => [
            CommandFlagStorage::PRIVATE_MESSAGE,
            CommandFlagStorage::IGNORE_ARGUMENTS,
        ],
        BaseAccessStorage::ADMIN => [
            CommandFlagStorage::OTHER_USER,
            CommandFlagStorage::PRIVATE_MESSAGE,
            CommandFlagStorage::IGNORE_ARGUMENTS,
        ],
        BaseAccessStorage::OWNER => [
            CommandFlagStorage::OTHER_USER,
            CommandFlagStorage::PRIVATE_MESSAGE,
            CommandFlagStorage::IGNORE_ARGUMENTS,
        ],
        BaseAccessStorage::DEVELOPER => CommandFlagStorage::ALL_FLAGS,
    ];
}
