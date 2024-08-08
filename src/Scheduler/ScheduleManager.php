<?php

namespace Discord\Bot\Scheduler;

use Discord\Bot\Core;
use Discord\Bot\Scheduler\Interface\QueueManagerInterface;
use Discord\Bot\Scheduler\Parts\AbstractTask;
use Discord\Bot\Scheduler\Parts\DefaultTask;
use Discord\Bot\Scheduler\Parts\Executor;
use Discord\Bot\Scheduler\Parts\PeriodicTask;
use Discord\Bot\Scheduler\Storage\QueueGroupStorage;
use Discord\Bot\Scheduler\Storage\TaskTypeStorage;
use Loader\System\Traits\ContainerTrait;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

class ScheduleManager
{
    use ContainerTrait;

    protected int $executeInterval = 900;

    protected QueueManagerInterface $queueManager;

    protected LoopInterface $loop;

    /**
     * @var array<TimerInterface>
     */
    protected array $taskInLoop = [];

    public function __construct(QueueManager $queueManager)
    {
        $this->queueManager = $queueManager;

        $this->initConfigTasks(__DIR__ . '/config/tasks.php');
    }

    public function start(): void
    {
        $this->loop->addPeriodicTimer($this->getExecuteInterval(), function () {
            $this->execute();
        });
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
            if ($task instanceof PeriodicTask && !empty($this->loop)) {
                if (!empty($this->taskInLoop[$task->getName()]) && !$this->queueManager->hasTask($task->getName())) {
                    $this->loop->cancelTimer($this->taskInLoop[$task->getName()]);

                    unset($this->taskInLoop[$task->getName()]);

                    continue;
                }

                if (!empty($this->taskInLoop[$task->getName()])) {
                    continue;
                }

                if ($task->getPeriodicInterval() !== 0) {
                    $timer = $this->loop->addPeriodicTimer($task->getPeriodicInterval(), function () use ($task) {
                        $this->executeTask($task);
                    });

                    $this->taskInLoop[$task->getName()] = $timer;
                } else {
                    trigger_error("{$task->getName()} interval not found");
                }

                continue;
            }

            if (!$this->executeTask($task)) {
                trigger_error("fail execute {$task->getName()}");
            }
        }
    }

    public function executeTask(AbstractTask $task): bool
    {
        return $task->getExecutor()->execute();
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
            if (empty($configTask['type'])) {
                $configTask['type'] = TaskTypeStorage::DEFAULT;
            }

            if (!is_callable($configTask['handler'])) {
                continue;
            }

            [$classOrObject, $method] = $configTask['handler'];

            if (!is_object($classOrObject)) {
                if (!class_exists($classOrObject)) {
                    continue;
                }

                $object = $this->getContainer()->createObject($classOrObject);

                $configTask['handler'] = [$object, $method];
            }

            $executor = (new Executor())->setCallable($configTask['handler']);

            if (!empty($configTask['arguments']) && is_array($configTask['arguments'])) {
                $executor->setArguments($configTask['arguments']);
            }

            if ($configTask['type'] === TaskTypeStorage::PERIODIC) {
                $task = (new PeriodicTask())
                    ->setName($name)
                    ->setExecutor($executor)
                    ->setQueueGroup(QueueGroupStorage::PERIODIC)
                ;

                if (!empty($configTask['interval'])) {
                    $task->setPeriodicInterval($configTask['interval']);
                }
            } else {
                $task = (new DefaultTask())
                    ->setName($name)
                    ->setExecutor($executor)
                ;
            }

            $this->addTask($task);
        }

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
}
