<?php

namespace Discord\Bot\Components\Access\Services;

use Discord\Bot\Components\Access\Entity\AccessEntity;
use Discord\Bot\Components\Access\Repositories\AccessRepository;
use Discord\Bot\Components\Access\Storage\BaseAccessStorage;
use Doctrine\DBAL\Exception;

class BaseAccessService
{
    protected AccessRepository $repository;

    public function __construct(AccessRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function createAccessGroup(string $userId, int $group = BaseAccessStorage::USER): bool
    {
        $entity = new AccessEntity();

        $entity->ac_usr_id = $userId;
        $entity->ac_group_lvl = $group;

        return $this->repository->saveByEntity($entity);
    }

    /**
     * @throws Exception
     */
    public function updateUserAccessGroup(string $userId, int $group): bool
    {
        return $this->repository->update(
            ['ac_group_lvl' => $group],
            ['ac_usr_id' => $userId]
        );
    }

    public function isRoot(int $access): bool
    {
        return $access > BaseAccessStorage::DEVELOPER;
    }
}
