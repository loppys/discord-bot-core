<?php

namespace Discord\Bot\Components\Voice\Repository;

use Discord\Bot\Components\Voice\Entity\VoiceQueueEntity;
use Discord\Bot\System\Repository\AbstractRepository;

class VoiceQueueRepository extends AbstractRepository
{
    protected string $entityClass = VoiceQueueEntity::class;

    public function createEntity(array $criteria = []): ?VoiceQueueEntity
    {
        return parent::createEntity($criteria);
    }
}