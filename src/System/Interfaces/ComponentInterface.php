<?php

namespace Discord\Bot\System\Interfaces;

interface ComponentInterface extends ComponentInfoInterface, ComponentLicenseInterface
{
    public function getService(): mixed;
}
