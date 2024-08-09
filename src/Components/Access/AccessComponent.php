<?php

namespace Discord\Bot\Components\Access;

use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\Access\Repositories\AccessRepository;
use Discord\Bot\Components\Access\Services\BaseAccessService;
use Discord\Bot\Components\Access\Storage\BaseAccessStorage;
use Doctrine\DBAL\Exception;

class AccessComponent extends AbstractComponent
{
    protected array $migrationList = [
        __DIR__ . '/Migrations/access_install.sql'
    ];

    public function __construct(AccessRepository $repository, BaseAccessService $service)
    {
        parent::__construct($repository, $service);
    }

    /**
     * @throws Exception
     */
    public function createAccessByUserId(string $userId, int $accessGroup = BaseAccessStorage::USER): bool
    {
        return $this->getService()->createAccessGroup($userId, $accessGroup);
    }

    /**
     * @throws Exception
     */
    public function updateUserAccessGroup(string $userId, int $newAccessGroup): bool
    {
        return $this->getService()->updateUserAccessGroup($userId, $newAccessGroup);
    }

    public function userIsRoot(int $userAccess): bool
    {
        return $this->getService()->isRoot($userAccess);
    }

    public function getRepository(): AccessRepository
    {
        return $this->repository;
    }

    public function getService(): BaseAccessService
    {
        return $this->service;
    }
}
