<?php

namespace Discord\Bot\Components\User\Services;

use Discord\Bot\Components\Access\Services\BaseAccessService;
use Discord\Bot\Components\Access\Storage\BaseAccessStorage;
use Discord\Bot\Components\Stat\Services\StatService;
use Discord\Bot\Components\User\Entity\User;
use Discord\Bot\Components\User\Repositories\UserRepository;
use Doctrine\DBAL\Exception;

class UserService
{
    protected UserRepository $repository;

    protected BaseAccessService $accessService;

    public function __construct(
        UserRepository $repository,
        BaseAccessService $accessService
    ) {
        $this->repository = $repository;
        $this->accessService = $accessService;
    }

    /**
     * @throws Exception
     */
    public function hasUser(string $id): bool
    {
        return $this->repository->has(['usr_id' => $id]);
    }

    /**
     * @throws Exception
     */
    public function getUserEntity(string $userId, string $serverId): ?User
    {
        $entity = $this->repository->createEntity(['usr_id' => $userId]);

        if ($entity === null) {
            return null;
        }

        $userAccess = $this->accessService->getUserAccessGroup($userId, $serverId);

        if ($userAccess === null) {
            if (!$this->accessService->createAccessGroup($userId, $serverId)) {
                return null;
            }

            $userAccess = $this->accessService->getUserAccessGroup($userId, $serverId);

            if ($userAccess === null) {
                return null;
            }

            $entity->ac_group_lvl = $userAccess->ac_group_lvl;
        }

        return $entity;
    }

    /**
     * @throws Exception
     */
    public function registerUser(string $userId, string $serverId, int $group = BaseAccessStorage::USER): ?User
    {
        if ($this->hasUser($userId)) {
            return null;
        }

        if (!$this->repository->save(['usr_id' => $userId])) {
            return null;
        }

        if (!$this->accessService->createAccessGroup($userId, $serverId, $group)) {
            return null;
        }

        return $this->repository->createEntity([
            'usr_id' => $userId
        ]);
    }

    public function updateUser(string $userId, array $updateData = []): bool
    {
        if (empty($updateData)) {
            return false;
        }

        return $this->repository->update($updateData, [
            'usr_id' => $userId
        ]);
    }
}
