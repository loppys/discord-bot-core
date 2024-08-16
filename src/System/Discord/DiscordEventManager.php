<?php

namespace Discord\Bot\System\Discord;

use Discord\Bot\Core;
use Discord\Bot\System\ComponentsFacade;
use Discord\Bot\System\Discord\Events\AbstractEvent;
use Discord\Bot\System\Discord\Events\JoinUserEvent;
use Discord\Bot\System\Discord\Events\MessageCreateEvent;
use Discord\Bot\System\Discord\Events\ReadyEvent;
use Loader\System\Traits\ContainerTrait;
use Discord\WebSockets\Event;
use Discord\Discord;

class DiscordEventManager
{
    use ContainerTrait;

    protected Discord $discord;

    /**
     * @var array<string>
     */
    protected array $defaultEvents = [
        'ready' => ReadyEvent::class,
//        Event::INTERACTION_CREATE => InteractionEvent::class,
        Event::MESSAGE_CREATE => MessageCreateEvent::class,
//        Event::MESSAGE_UPDATE => MessageEvent::class,
//        Event::MESSAGE_DELETE => MessageEvent::class,
//        Event::VOICE_STATE_UPDATE => VoiceEvent::class,
//        Event::PRESENCE_UPDATE => PresenceEvent::class,
        Event::GUILD_MEMBER_ADD => JoinUserEvent::class,
    ];

    /**
     * @var array<AbstractEvent>
     */
    protected array $registeredEventList = [];

    public function initDiscord(Discord $discord): static
    {
        $this->discord = $discord;

        return $this;
    }

    public function initDefaultEvents(): static
    {
        if (empty($this->discord)) {
            return $this;
        }

        foreach ($this->defaultEvents as $name => $event) {
            $this->registerDiscordEvent($name, $event);
        }

        return $this;
    }

    public function registerDiscordEvent(string $eventName, string $eventClass): static
    {
        if (!empty($this->registeredEventList[$eventName])) {
            return $this;
        }

        $event = new $eventClass;

        if (!$event instanceof AbstractEvent) {
            return $this;
        }

        $event->setComponents(
            Core::getInstance()->components
        );

        $this->discord->on($eventName, $event->getCallable());

        $this->registeredEventList[$eventName] = $event;

        return $this;
    }
}
