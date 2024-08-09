<?php

namespace Discord\Bot\Components\User;

use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\User\Repositories\UserRepository;
use Discord\Bot\Components\User\Services\UserService;

class UserComponent extends AbstractComponent
{
    public function __construct(UserRepository $repository, UserService $service)
    {
        parent::__construct($repository, $service);
    }

    public function getRepository(): UserRepository
    {
        return $this->repository;
    }

    public function getService(): UserService
    {
        return $this->service;
    }
}
