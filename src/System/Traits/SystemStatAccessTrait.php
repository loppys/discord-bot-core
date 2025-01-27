<?php

namespace Discord\Bot\System\Traits;

use Discord\Bot\System\Storages\TypeSystemStat;
use Discord\Bot\System\SystemStat;
use Loader\System\Container;

trait SystemStatAccessTrait
{
    public function getSystemStat(): SystemStat
    {
        return Container::getInstance()->getShared('systemStat')
            ?? Container::getInstance()->createObject(SystemStat::class);
    }
}
