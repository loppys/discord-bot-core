<?php

namespace Discord\Bot\System\Traits;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\DI\Exceptions\NotFoundException;
use Vengine\Libs\DI\traits\ContainerAwareTrait;

trait ContainerInjection
{
    use ContainerAwareTrait;
    use Injectable;

    /**
     * @noinspection MagicMethodsValidityInspection
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function __get($name): mixed
    {
        return $this->getContainer()->get($name);
    }
}
