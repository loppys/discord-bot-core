<?php

namespace Discord\Bot\System\Interfaces;

interface ComponentInterface extends ComponentInfoInterface
{
    public function getService(): mixed;
}
