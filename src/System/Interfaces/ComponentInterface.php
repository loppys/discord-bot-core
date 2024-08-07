<?php

namespace Discord\Bot\System\Interfaces;

interface ComponentInterface extends ComponentInfoInterface
{
    /**
     * @return RepositoryInterface
     */
    public function getRepository(): mixed;

    /**
     * @return ComponentServiceInterface
     */
    public function getService(): mixed;
}
