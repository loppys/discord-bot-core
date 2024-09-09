<?php

namespace Discord\Bot;

class SystemCheck
{
    /**
     * @var array<string>
     */
    protected array $tasks = [
        'discordRoleSync'
    ];

    /**
     * Сколько раз необходимо запустить SystemCheck, чтобы задача повторно была запущена
     * По умолчанию значение = 1 (т.е. каждый запуск SystemCheck)
     */
    protected array $tasksIntervalCount = [
        'discordRoleSync' => 2
    ];

    /**
     * Ключ - название задачи
     * Значение - количество запусков SystemCheck
     *
     * @var array<string, int>
     */
    private array $runCount = [];

    public function run(): bool
    {
        foreach ($this->tasks as $task) {
            if (!method_exists($this, $task)) {
                continue;
            }

            if (empty($this->runCount[$task])) {
                $this->runCount[$task] = 1;
            }

            if (empty($this->tasksIntervalCount[$task])) {
                $this->tasksIntervalCount[$task] = 1;
            }

            if (
                $this->tasksIntervalCount[$task] > 1
                && (!empty($this->runCount[$task]) && $this->runCount[$task] > 0)
            ) {
                if ($this->tasksIntervalCount[$task] !== $this->runCount[$task]) {
                    $this->runCount[$task]++;

                    continue;
                }

                $this->runCount[$task] = 0;
            }

            $this->$task();

            $this->runCount[$task]++;
        }

        return true;
    }

    public function discordRoleSync(): bool
    {
        return true;
    }
}
