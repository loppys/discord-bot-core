<?php

namespace Discord\Bot\Components\Voice\Repository;

use Discord\Bot\Components\Voice\Entity\VoiceRoom;
use Discord\Bot\System\Repository\AbstractRepository;

class VoiceRoomRepository extends AbstractRepository
{
    protected string $entityClass = VoiceRoom::class;

    public function createEntity(array $criteria = []): ?VoiceRoom
    {
        return parent::createEntity($criteria);
    }
}