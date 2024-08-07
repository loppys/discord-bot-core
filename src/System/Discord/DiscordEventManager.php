<?php

namespace App\System\Discord;

use App\System\Discord\Events\AbstractEvent;
use Discord\Bot\Core;
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
        'ready' => 'class',
//        Event::INTERACTION_CREATE => InteractionEvent::class,
//        Event::MESSAGE_CREATE => MessageEvent::class,
//        Event::MESSAGE_UPDATE => MessageEvent::class,
//        Event::MESSAGE_DELETE => MessageEvent::class,
//        Event::VOICE_STATE_UPDATE => VoiceEvent::class,
//        Event::PRESENCE_UPDATE => PresenceEvent::class,
//        Event::GUILD_MEMBER_ADD => JoinMemberEvent::class,
    ];

    /**
     * @var array<AbstractEvent>
     */
    protected array $registeredEventList = [];

    public function __construct()
    {
        $this->container = $this->getContainer();
        $this->discord = Core::getInstance()->getDiscord();

        foreach ($this->defaultEvents as $name => $event) {
            $this->registerDiscordEvent($name, $event);
        }
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

        if ($event->getName() !== $eventName) {
            $this->discord->on($eventName, $event->getCallable($eventName));
        } else {
            $this->discord->on($eventName, $event->getCallable());
        }

        $this->registeredEventList[$eventName] = $event;

        return $this;
    }
}
