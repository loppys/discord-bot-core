<?php

namespace Discord\Bot\Components\User;

use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\Access\Storage\BaseAccessStorage;
use Discord\Bot\Components\User\Entity\User;
use Discord\Bot\Components\User\Services\UserService;
use Doctrine\DBAL\Exception;

/**
 * @method UserService getService()
 */
class UserComponent extends AbstractComponent
{
    protected string $mainServiceClass = UserService::class;

    protected array $migrationList = [
        __DIR__ . '/Migrations/user_table.sql'
    ];

    /**
     * @throws Exception
     */
    public function hasUser(string $id): bool
    {
        return $this->getService()->hasUser($id);
    }

    /**
     * @throws Exception
     */
    public function getUser(string $id, string $server): ?User
    {
        return $this->getService()->getUserEntity($id, $server);
    }

    /**
     * @throws Exception
     */
    public function register(string $userId, string $serverId, int $group = BaseAccessStorage::USER): ?User
    {
        return $this->getService()->registerUser($userId, $serverId, $group);
    }

    public function updateUser(string $userId, array $updateData = []): bool
    {
        return $this->getService()->updateUser($userId, $updateData);
    }
}
