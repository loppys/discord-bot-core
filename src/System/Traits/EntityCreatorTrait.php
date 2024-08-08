<?php

namespace Discord\Bot\System\Traits;

use Discord\Bot\System\Repository\Entity\AbstractEntity;
use RuntimeException;

trait EntityCreatorTrait
{
    protected function create(string $entityClass, array $entityData = []): ?AbstractEntity
    {
        if (!class_exists($entityClass)) {
            throw new RuntimeException("class {$entityClass} not found");
        }

        $entity = new $entityClass();

        if ($entity instanceof AbstractEntity) {
            return $entity->setEntityData($entityData);
        }

        return null;
    }
}
