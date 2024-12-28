<?php

namespace Discord\Bot\Components\Command\Parts;

use Discord\Bot\Components\Access\Storage\BaseAccessStorage;
use Discord\Bot\Components\Command\DTO\Command;
use Discord\Bot\Components\Command\DTO\ExecuteResult;
use Discord\Bot\Components\Command\Interfaces\CascadeCommandInterface;
use Discord\Bot\Core;
use Discord\Bot\System\ComponentsFacade;
use Vengine\Libraries\DBAL\Adapter;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Interaction;
use Doctrine\DBAL\Exception;
use Loader\System\Container;

abstract class AbstractProcessCommand
{
    public bool $isNewScheme = false;

    public static int $access = BaseAccessStorage::USER;

    protected Command $command;

    protected Message|null $message = null;

    protected ComponentsFacade $components;

    protected array $arguments = [];

    protected array $flags = [];

    protected Adapter $db;

    protected Discord $discord;

    protected Interaction $interaction;

    protected Container $container;

    private array $errorList = [];

    /**
     * @var array<ExecuteResult>
     */
    private array $resultList = [];

    public function __construct(Discord $discord, Adapter $db)
    {
        $this->container = Container::getInstance();
        $this->discord = $discord;
        $this->db = $db;
    }

    /**
     * @throws NoPermissionsException
     * @throws Exception
     */
    public function process(
        Message $message,
        Command $command
    ): bool {
        $this->message = $message;
        $this->command = $command;
        $this->flags = $command->getFlags();
        $this->arguments = $command->getArguments();

        if (!$this->execute()) {
            // На случай, если будет переопределение
            if ($this->message === null) {
                return false;
            }

            $errorList = $this->getErrorList();

            if (empty($errorList)) {
                $this->message->channel->sendMessage('Что-то пошло не так!');

                return false;
            }

            foreach ($errorList as $error) {
                if ($error['reply']) {
                    $this->message->reply($error['error']);

                    continue;
                }

                $this->message->channel->sendMessage($error['error']);
            }
        }

        $commandComponent = Core::getInstance()->components->command;
        if ($this instanceof CascadeCommandInterface) {
            foreach ($this::COMMAND_LIST as $name) {
                if (!is_string($name)) {
                    continue;
                }

                // Пока работает только со старой схемой вызова
                $this->message->content = str_replace(
                    $this->command->getCommandName(),
                    $name,
                    $this->message->content
                );

                $this->resultList[$name] = $commandComponent->execute($this->message);
            }
        }

        return true;
    }

    public function addError(string $error, bool $reply = false): static
    {
        $this->errorList[] = [
            'error' => $error,
            'reply' => $reply
        ];

        return $this;
    }

    /**
     * @return array<ExecuteResult>
     */
    public function getResultList(): array
    {
        return $this->resultList;
    }

    public function getErrorList(): array
    {
        $e = $this->errorList;

        $this->errorList = [];

        return $e;
    }

    public function error(string $message = '', bool $reply = false): bool
    {
        if (!empty($message)) {
            $this->addError($message, $reply);
        }

        return false;
    }

    /**
     * @throws NoPermissionsException
     */
    public function success(string|MessageBuilder $message = null, bool $reply = false): bool
    {
        if ($this->message !== null && !empty($message)) {
            if ($reply) {
                $this->message->reply($message);
            } else {
                $this->message->channel->sendMessage($message);
            }
        }

        return true;
    }

    public function setInteraction(Interaction $interaction): void
    {
        $this->isNewScheme = true;

        $this->interaction = $interaction;
    }

    public function setComponents(ComponentsFacade $components): void
    {
        $this->components = $components;
    }

    abstract protected function execute(): bool;
}
