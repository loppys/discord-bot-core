<?php

namespace Discord\Bot\System\Discord\Events;

use Discord\Discord;
use Discord\Parts\User\Activity;

class ReadyEvent extends AbstractEvent
{
    public function ready(Discord $discord): bool
    {
        $activity = new Activity($discord);

        $activity->name = "v2.0.5";
        $activity->type = Activity::TYPE_PLAYING;

        $discord->updatePresence($activity, false, Activity::STATUS_ONLINE);

        return $this->success();
    }
}