<?php

namespace Discord\Bot\Components\Command;

use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\Command\DTO\ExecuteResult;
use Discord\Bot\Components\Command\Entity\CommandEntity;
use Discord\Bot\Components\Command\Repositories\CommandRepository;
use Discord\Bot\Components\Command\Services\CommandService;
use Discord\Bot\Scheduler\Storage\TaskTypeStorage;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Interaction;
use Doctrine\DBAL\Exception;


class CommandComponent extends AbstractComponent
{
    protected array $migrationList = [
        __DIR__ . '/Migrations',
    ];

    protected array $scheduleTasks = [
        'system-check' => [
            'handler' => [CommandService::class, 'syncCommands'],
            'interval' => 900,
            'type' => TaskTypeStorage::PERIODIC,
        ],
    ];

    public function __construct(CommandService $service)
    {
        parent::__construct($service);
    }

    /**
     * @throws Exception
     * @throws NoPermissionsException
     */
    public function execute(Message $message): ExecuteResult
    {
        return $this->getService()->execute($message);
    }

    /**
     * @throws NoPermissionsException
     * @throws Exception
     */
    public function executeNewScheme(Interaction $interaction): ExecuteResult
    {
        return $this->getService()->executeNewScheme($interaction);
    }

    /**
     * @throws Exception
     */
    public function addCommand(array|CommandEntity $command): bool
    {
        return $this->getService()->addCommand($command);
    }

    public function getService(): CommandService
    {
        return $this->service;
    }
}
