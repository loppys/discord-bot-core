<?php

namespace Discord\Bot\System\Traits;

use Loader\System\Traits\ContainerTrait;

trait ContainerInjection
{
    use ContainerTrait;
    use Injectable;

    /** @noinspection MagicMethodsValidityInspection */
    public function __get($name): mixed
    {
        return $this->getContainer()->getShared($name);
    }
}
