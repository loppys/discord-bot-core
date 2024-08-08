<?php

namespace Discord\Bot\Components\Command\Parts;

use Discord\Bot\Components\Access\Storage\BaseAccessStorage;
use Discord\Bot\Components\Command\Interfaces\CascadeCommandInterface;
use Discord\Bot\System\DBAL;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Loader\System\Container;

abstract class AbstractProcessCommand
{
    public static int $access = BaseAccessStorage::USER;

    public Message|null $message = null;

    /*
     * [arguments, flags]
     */
    public array $arguments = [];

    public DBAL $db;

    public Discord $discord;

    protected Container $container;

    private array $errorList = [];

    public function __construct(Discord $discord, DBAL $db)
    {
        $this->container = Container::getInstance();
        $this->discord = $discord;
        $this->db = $db;
    }

    public function process(...$arguments): void
    {
        if (is_array($arguments[0])) {
            $this->arguments = $arguments[0] ?: [];
        }

        if ($arguments[1] instanceof Message) {
            $this->message = $arguments[1];
        }

        if ($this->message === null) {
            return;
        }

        if (!$this->execute()) {
            if ($this->message === null) {
                return;
            }

            $errorList = $this->getErrorList();

            if (empty($errorList)) {
                $this->message->channel->sendMessage('Что-то пошло не так!');

                return;
            }

            foreach ($errorList as $error) {
                if ($error['reply']) {
                    $this->message->reply($error['error']);

                    continue;
                }

                $this->message->channel->sendMessage($error['error']);
            }
        }

        ConsoleMessageHelper::showTechMessage("execute_command_class", static::class);

        if ($this instanceof CascadeCommandInterface) {
            foreach ($this::COMMAND_LIST as $name => $class) {
                if (!is_string($name) || !class_exists($class)) {
                    continue;
                }

                $cmdManager = CommandManager::getInstance();

                $command = $cmdManager::parse($this->message->content);

                if ($command === null) {
                    continue;
                }

                $command
                    ->setClass($class)
                    ->setName($name)
                ;

                $currentClass = static::class;
                $currentUsername = $this->message->author->username;
                $this->message->author->username = "cascade:call => {$class}|initiator => {$currentClass}|user => {$currentUsername}";

                $cmdManager->execute($command, $this->message);
            }
        }
    }

    public function addError(string $error, bool $reply = false): static
    {
        $this->errorList[] = [
            'error' => $error,
            'reply' => $reply
        ];

        return $this;
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

    abstract protected function execute(): bool;
}