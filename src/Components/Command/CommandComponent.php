<?php

namespace Discord\Bot\Components\Command;

use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\Command\Repositories\CommandRepository;
use Discord\Bot\Components\Command\Services\CommandService;


class CommandComponent extends AbstractComponent
{
    public function getRepository(): CommandRepository
    {
        return $this->repository;
    }

    public function getService(): CommandService
    {
        return $this->service;
    }
}
