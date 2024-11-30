<?php

namespace Discord\Bot\Components\Command\Services;

use Discord\Bot\Components\Command\DTO\Command;
use Discord\Bot\Components\Command\DTO\CommandMigration;
use Discord\Bot\Components\Command\DTO\ExecuteResult;
use Discord\Bot\Components\Command\Entity\CommandEntity;
use Discord\Bot\Components\Command\Parts\AbstractProcessCommand;
use Discord\Bot\Components\Command\Repositories\CommandRepository;
use Discord\Bot\Components\Command\Storage\CommandMigrationTypeStorage;
use Discord\Bot\Scheduler\Interface\QueueManagerInterface;
use Discord\Bot\Scheduler\QueueManager;
use Discord\Bot\System\Helpers\ConsoleLogger;
use Discord\Parts\Interactions\Command\Command as DiscordCommand;
use Discord\Bot\Config;
use Discord\Bot\Core;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Interactions\Interaction;
use Doctrine\DBAL\Exception as DBException;
use Exception;
use Loader\System\Container;

class CommandService
{
    protected CommandRepository $commandRepository;

    protected QueueManagerInterface $queueManager;

    public function __construct(CommandRepository $commandRepository)
    {
        $this->commandRepository = $commandRepository;
        $this->queueManager = new QueueManager();
    }

    /**
     * @throws DBException
     */
    public function getCommandByName(string $name): ?CommandEntity
    {
        return $this->commandRepository->createEntity(['name' => $name]);
    }

    /**
     * @throws DBException
     */
    public function addCommand(array|CommandEntity $command): bool
    {
        if (is_array($command)) {
            $commandEntity = $this->commandRepository->createEntityByArray($command);

            if (!$commandEntity instanceof CommandEntity) {
                if (!$commandEntity instanceof CommandMigration) {
                    ConsoleLogger::showMessage("command add fail 1x01: {$command->name}");

                    return false;
                }
            }

            $command = $commandEntity;
        }

        if (!$command->isCommandClassExists()) {
            ConsoleLogger::showMessage("command add fail 1x02: {$command->name}");

            return false;
        }

        if (empty($command->name)) {
            ConsoleLogger::showMessage("command add fail 1x03: {$command->name}");

            return false;
        }

        if ($this->commandRepository->has(['name' => $command->name])) {
            ConsoleLogger::showMessage("command add fail 1x04: {$command->name}");

            return false;
        }

        ConsoleLogger::showMessage("add command: {$command->name}");

        return $this->commandRepository->saveByEntity($command);
    }

    /**
     * @throws DBException
     * @throws Exception
     */
    public function syncCommands(): bool
    {
        $guilds = Core::getInstance()->getDiscord()->guilds->toArray();

        /** @var Guild $guild */
        foreach ($guilds as $guild) {
            /** @var CommandEntity $commandEntity */
            foreach ($this->commandRepository->getAll() as $commandEntity) {
                if (!$guild->commands->has($commandEntity->name)) {
                    $discordCommandDTO = new DiscordCommand(
                        Core::getInstance()->getDiscord()
                    );

                    $discordCommandDTO->id = "command:{$commandEntity->name}";
                    $discordCommandDTO->name = $commandEntity->name;
                    $discordCommandDTO->description = $commandEntity->description;

                    $guild->commands->save($discordCommandDTO);
                }
            }

            /** @var DiscordCommand $val */
            foreach ($guild->commands->toArray() as $val) {
                if (!$this->commandRepository->has(['name' => $val->name])) {
                    $guild->commands->delete($val);
                }
            }
        }

        return true;
    }

    /**
     * @throws Exception
     * @throws NoPermissionsException
     * @throws DBException
     */
    public function execute(Message $message, ?Interaction $interaction = null): ExecuteResult
    {
        $command = $this->compareCommandByMessage($message->content);

        if (!$this->hasCommand($command)) {
            return ExecuteResult::create('Команда не найдена.', '9010', false);
        }

        $entity = $this->getCommandByName($command->getCommandName());

        if ($entity === null) {
            return ExecuteResult::create('Не удалось создать команду.', '9011', false);
        }

        if (!$entity->isCommandClassExists()) {
            return ExecuteResult::create('Не удалось выполнить команду.', '9020', false);
        }

        if ($interaction === null && $entity->isNewScheme()) {
            $commandName = $command->getCommandName();
            $cmdSymbol = Config::getSymbolCommand();

            return ExecuteResult::create(
                "{$cmdSymbol}{$commandName} использует устаревшую схему вызова, используйте /{$commandName}",
                '11022',
                false
            );
        }

        $commandProcess = Core::getInstance()
            ->getContainer()
            ->createObject($entity->class)
        ;

        if (!$commandProcess instanceof AbstractProcessCommand) {
            return ExecuteResult::create(
                'Команда сформирована некорректно',
                '22011',
                false
            );
        }

        if ($interaction !== null && $entity->isNewScheme()) {
            $commandProcess->setInteraction($interaction);
        }

        $result = $commandProcess->process(
            $message,
            $command
        );

        if ($result) {
            return ExecuteResult::create(
                '',
                '1',
                true
            );
        }

        return ExecuteResult::create(
            'Команда выполнена с ошибками',
            '00192',
            false
        );
    }

    /**
     * @throws NoPermissionsException
     * @throws DBException
     */
    public function executeNewScheme(Interaction $interaction): ExecuteResult
    {
        if ($interaction->message === null) {
            return new ExecuteResult();
        }

        return $this->execute($interaction->message, $interaction);
    }

    /**
     * @throws Exception
     * @throws DBException
     */
    protected function hasCommand(Command $command): bool
    {
        return $this->commandRepository->has([
            'name' => $command->getCommandName()
        ]);
    }

    public function compareCommandByMessage(string $message): Command
    {
        return new Command($message);
    }

    public function addCommandMigration(CommandMigration $migration): static
    {
        $this->queueManager->addTask($migration);

        return $this;
    }

    /**
     * @param array<CommandMigration>|null $migrations
     * @return void
     * @throws DBException
     */
    public function executeCommandMigration(?array $migrations = null): bool
    {
        /** @var CommandMigration $item */
        foreach ($migrations ?? $this->queueManager->compareQueue() as $item) {
            if (!is_object($item)) {
                $item = Container::getInstance()->createObject($item);
            }

            if (!$item instanceof CommandMigration) {
                continue;
            }

            if ($this->commandRepository->has(['name' => $item->name])) {
                continue;
            }

            ConsoleLogger::showMessage("execute command migration: {$item->class}");

            match ($item->getType()) {
                CommandMigrationTypeStorage::CREATE => $this->addCommand($item),
                CommandMigrationTypeStorage::UPDATE => $this->commandRepository->update(
                    $item->toArray(),
                    ['name' => $item->name]
                ),
                CommandMigrationTypeStorage::DELETE => $this->commandRepository->delete(
                    ['name' => $item->name]
                ),
                default => ConsoleLogger::showMessage("type ({$item->getType()}) not supported")
            };
        }

        return true;
    }
}
