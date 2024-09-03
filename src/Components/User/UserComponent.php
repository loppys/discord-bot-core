<?php

namespace Discord\Bot\Components\User;

use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\Access\Storage\BaseAccessStorage;
use Discord\Bot\Components\User\Entity\User;
use Discord\Bot\Components\User\Repositories\UserRepository;
use Discord\Bot\Components\User\Services\UserService;
use Doctrine\DBAL\Exception;

class UserComponent extends AbstractComponent
{
    protected array $migrationList = [
        __DIR__ . '/Migrations/user_table.sql'
    ];

    public function __construct(UserService $service)
    {
        parent::__construct($service);
    }

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
    public function register(string $id, string $server, int $group = BaseAccessStorage::USER): ?User
    {
        return $this->getService()->registerUser($id, $server, $group);
    }

    public function updateUser(string $userId, array $updateData = []): bool
    {
        return $this->getService()->updateUser($userId, $updateData);
    }

    public function getService(): UserService
    {
        return $this->service;
    }
}
