<?php

namespace Discord\Bot\System\Discord\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Command\Command;
use Discord\WebSockets\Event;
use React\Promise\ExtendedPromiseInterface;

class MessageCreateEvent extends AbstractEvent
{
    protected string $name = Event::MESSAGE_CREATE;

    protected string $callbackMethod = 'create';

    public function create(Message $message, Discord $discord): ExtendedPromiseInterface|bool
    {
        return $this->success();
    }
}
