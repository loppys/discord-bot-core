<?php

namespace Discord\Bot\Components\Access;

use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\Access\Repositories\AccessRepository;
use Discord\Bot\Components\Access\Services\BaseAccessService;
use Discord\Bot\Components\Access\Storage\BaseAccessStorage;
use Doctrine\DBAL\Exception;

/**
 * @method BaseAccessService getService()
 */
class AccessComponent extends AbstractComponent
{
    protected string $mainServiceClass = BaseAccessService::class;

    protected array $migrationList = [
        __DIR__ . '/Migrations/access_install.sql'
    ];

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
    public function updateUserAccessGroup(string $userId, string $server, int $newAccessGroup): bool
    {
        return $this->getService()->updateUserAccessGroup($userId, $server, $newAccessGroup);
    }

    public function userIsRoot(int $userAccess): bool
    {
        return $this->getService()->isRoot($userAccess);
    }

    public function repository(): AccessRepository
    {
        return $this->getService()->getRepository();
    }
}
