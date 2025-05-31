<?php

namespace Discord\Bot\Scheduler;

use Discord\Bot\Core;
use Discord\Bot\Scheduler\Interface\QueueManagerInterface;
use Discord\Bot\Scheduler\Parts\AbstractTask;
use Discord\Bot\Scheduler\Parts\DefaultTask;
use Discord\Bot\Scheduler\Parts\Executor;
use Discord\Bot\Scheduler\Parts\PeriodicTask;
use Discord\Bot\Scheduler\Storage\ExecuteSchemeStorage;
use Discord\Bot\Scheduler\Storage\QueueGroupStorage;
use Discord\Bot\Scheduler\Storage\TaskTypeStorage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Vengine\Libraries\Console\ConsoleLogger;
use Vengine\Libs\DI\Exceptions\ContainerException;
use Vengine\Libs\DI\Exceptions\NotFoundException;
use Vengine\Libs\DI\interfaces\ContainerAwareInterface;
use Vengine\Libs\DI\traits\ContainerAwareTrait;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

class ScheduleManager implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected int $executeInterval = 15;

    protected QueueManagerInterface $queueManager;

    protected LoopInterface $loop;

    /**
     * @var array<TimerInterface>
     */
    protected array $taskInLoop = [];

    /**
     * @throws ContainerException
     */
    public function __construct(QueueManager $queueManager)
    {
        ConsoleLogger::showMessage('create Scheduler');

        $this->queueManager = $queueManager;
        $this->setContainer(Core::getInstance()->getContainer());

        $this->initConfigTasks(__DIR__ . '/config/tasks.php');
    }

    public function start(): void
    {
        ConsoleLogger::showMessage('start Scheduler : interval = ' . $this->getExecuteInterval());

        $this->loop->addPeriodicTimer($this->getExecuteInterval(), function () {
            $this->execute();
        });
    }

    public function stop(): void
    {
        ConsoleLogger::showMessage('stop Scheduler');

        $this->loop->stop();
    }

    public function setQueueManager(QueueManagerInterface $queueManager): static
    {
        if (!empty($this->queueManager) && $this->queueManager->countTasks() > 0) {
            trigger_error('Changing the queue manager while there are active tasks is prohibited');

            return $this;
        }

        $this->queueManager = $queueManager;

        return $this;
    }

    public function addTask(AbstractTask $task): static
    {
        if ($task->getType() === TaskTypeStorage::PERIODIC && $task->getQueueGroup() !== QueueGroupStorage::PERIODIC) {
            $task->setQueueGroup(QueueGroupStorage::PERIODIC);
        }

        $this->queueManager->addTask($task);

        ConsoleLogger::mixedMessage(
            "add schedule task: {$task->getName()}",
            [
                'queueGroup' => $task->getQueueGroup(),
                'maxLaunches' => $task->getMaxLaunches() ?: 'unlimited',
                'time' => date('d.m.y H:i:s'),
            ]
        );

        return $this;
    }

    public function removeTask(string $name): static
    {
        if (!empty($this->taskInLoop[$name])) {
            $this->periodicTaskStop($name);
        }

        $this->queueManager->removeTaskByName($name);

        return $this;
    }

    public function getTaskByName(string $name): ?AbstractTask
    {
        $task = $this->queueManager->getTask($name);

        if ($task === null) {
            return null;
        }

        if (!$task instanceof AbstractTask) {
            return null;
        }

        return $task;
    }

    public function execute(): void
    {
        /** @var AbstractTask $task */
        foreach ($this->queueManager->compareQueue() as $task) {
            if ($task->isDone() && !empty($this->taskInLoop[$task->getName()])) {
                $this->removeTask($task->getName());

                continue;
            }

            $maxLaunches = $task->getMaxLaunches();
            if ($maxLaunches > 0 && $task->getLaunchesCount() > $maxLaunches) {
                trigger_error("task {$task->getName()} has reached its run limit");

                $this->removeTask($task->getName());

                continue;
            }

            if ($task instanceof PeriodicTask && !empty($this->loop)) {
                if (!empty($this->taskInLoop[$task->getName()]) && !$this->queueManager->hasTask($task->getName())) {
                    $this->periodicTaskStop($task->getName());

                    continue;
                }

                if (!empty($this->taskInLoop[$task->getName()])) {
                    continue;
                }

                if ($task->getPeriodicInterval() !== 0) {
                    $timer = $this->loop->addPeriodicTimer($task->getPeriodicInterval(), function () use ($task) {
                        if (!$this->executeTask($task)) {
                            if ($task->isDone()) {
                                trigger_error("fail execute {$task->getName()}");
                            }
                        }
                    });

                    $this->taskInLoop[$task->getName()] = $timer;
                } else {
                    trigger_error("{$task->getName()} interval not found");
                }
            }

            if (!$this->executeTask($task)) {
                if ($task->isDone()) {
                    trigger_error("fail execute {$task->getName()}");
                }
            }

            if (!$task->isDone()) {
                $this->queueManager->addTask($task);
            }
        }
    }

    public function executeTask(AbstractTask $task): bool
    {
        ConsoleLogger::showMessage("execute schedule task: {$task->getName()}");

        $result = $task->addLaunch()->getExecutor()->execute();

        if (
            ($result && $task->getType() !== TaskTypeStorage::PERIODIC)
            && $task::SCHEME === ExecuteSchemeStorage::AUTO
        ) {
            $task->done();
        }

        return $result && $task->isDone();
    }

    public function initConfigTasks(string $absolutePath): bool
    {
        if (!file_exists($absolutePath)) {
            return false;
        }

        $configTasks = require($absolutePath);

        if (!is_array($configTasks) || empty($configTasks)) {
            return false;
        }

        foreach ($configTasks as $name => $configTask) {
            if (!is_array($configTask)) {
                continue;
            }

            $this->initTaskByArray($configTask, $name);
        }

        return true;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function initTaskByArray(array $taskArray, string $name = ''): bool
    {
        if (empty($taskArray['name']) && !empty($name)) {
            $taskArray['name'] = $name;
        }

        if (empty($taskArray['name'])) {
            $taskArray['name'] = uniqid('task.', true);
        }

        if (empty($taskArray['type'])) {
            $taskArray['type'] = TaskTypeStorage::DEFAULT;
        }

        if (empty($taskArray['handler']) || !is_array($taskArray['handler'])) {
            return false;
        }

        [$classOrObject, $method] = $taskArray['handler'];

        if (!is_object($classOrObject)) {
            if (!class_exists($classOrObject)) {
                return false;
            }

            $object = $this->container->get($classOrObject);

            $taskArray['handler'] = [$object, $method];
        }

        $executor = (new Executor())->setCallable($taskArray['handler']);

        if (!empty($taskArray['arguments']) && is_array($taskArray['arguments'])) {
            $executor->setArguments($taskArray['arguments']);
        }

        if ($taskArray['type'] === TaskTypeStorage::PERIODIC) {
            $task = (new PeriodicTask())
                ->setName($taskArray['name'])
                ->setExecutor($executor)
                ->setQueueGroup(QueueGroupStorage::PERIODIC)
            ;

            if (!empty($taskArray['interval'])) {
                $task->setPeriodicInterval($taskArray['interval']);
            }
        } else {
            $task = (new DefaultTask())
                ->setName($taskArray['name'])
                ->setExecutor($executor)
            ;
        }

        $this->addTask($task);

        return true;
    }

    public function getExecuteInterval(): int
    {
        return $this->executeInterval;
    }

    public function setExecuteInterval(int $executeInterval): static
    {
        $this->executeInterval = $executeInterval;

        return $this;
    }

    public function setLoop(LoopInterface $loop): void
    {
        $this->loop = $loop;
    }

    public function periodicTaskStop(string $name): bool
    {
        if (empty($this->taskInLoop[$name])) {
            return false;
        }

        $this->loop->cancelTimer($this->taskInLoop[$name]);

        unset($this->taskInLoop[$name]);

        return true;
    }

    public function hasTask(string $name): bool
    {
        if (!empty($this->taskInLoop[$name])) {
            return true;
        }

        return $this->queueManager->hasTask($name);
    }

    public function getTasks(): array
    {
        return $this->queueManager->getTasks();
    }
}
