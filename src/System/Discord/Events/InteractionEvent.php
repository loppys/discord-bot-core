<?php

namespace Discord\Bot\System\Discord\Events;

use Discord\Parts\Interactions\Interaction;
use Discord\WebSockets\Event;

class InteractionEvent extends AbstractEvent
{
    protected string $name = Event::INTERACTION_CREATE;

    protected string $callbackMethod = 'interaction';

    public function interaction(Interaction $interaction): bool
    {
        return $this->success();
    }
}