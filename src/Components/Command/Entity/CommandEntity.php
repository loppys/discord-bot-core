<?php

namespace Discord\Bot\Components\Command\Entity;

use Discord\Bot\System\Repository\Entity\AbstractEntity;

/**
 * @property string $name
 * @property int $access
 * @property string $scheme
 * @property string $class
 * @property string $description
 */
class CommandEntity extends AbstractEntity
{
    public function isCommandClassExists(): bool
    {
        return class_exists($this->class);
    }

    public function isNewScheme(): bool
    {
        return $this->scheme === 'N';
    }
}
