<?php

namespace Discord\Bot\System\Discord\Events;

use Discord\Bot\Components\Access\Storage\BaseAccessStorage;
use Discord\Discord;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event;
use Doctrine\DBAL\Exception;

class JoinUserEvent extends AbstractEvent
{
    protected string $name = Event::GUILD_MEMBER_ADD;

    protected string $callbackMethod = 'join';

    /**
     * @throws Exception
     */
    public function join(Member $member, Discord $discord): bool
    {
        if ($this->components->user->hasUser($member->id)) {
            return $this->success();
        }

        $group = BaseAccessStorage::USER;

        if ($member->guild->owner_id === $member->id) {
            $group = BaseAccessStorage::OWNER;
        }

        $user = $this->components->user->register(
            $member->id,
            $member->guild_id,
            $group
        );

        if ($user === null) {
            return $this->fail();
        }

        return $this->success();
    }
}