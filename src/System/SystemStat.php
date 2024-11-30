<?php

namespace Discord\Bot\System;

use Discord\Bot\System\Helpers\ConsoleLogger;
use Discord\Bot\System\Storages\TypeSystemStat;

class SystemStat
{
    protected array $stats = [];

    protected array $tr = [
        TypeSystemStat::DB => 'Database queries',
        TypeSystemStat::CORE => 'use core',
        TypeSystemStat::COMPONENT => 'create components',
        TypeSystemStat::SCHEDULER => 'use scheduler',
        TypeSystemStat::MIGRATIONS => 'create && execute migrations',
        TypeSystemStat::MAIN => 'undefined',
    ];

    public function __construct()
    {
        $this->stats[TypeSystemStat::MAIN] = 0;
        $this->stats[TypeSystemStat::COMPONENT] = 0;
        $this->stats[TypeSystemStat::CORE] = 0;
        $this->stats[TypeSystemStat::SCHEDULER] = 0;
        $this->stats[TypeSystemStat::MIGRATIONS] = 0;
        $this->stats[TypeSystemStat::DB] = 0;
    }

    public function add(string|int $type = TypeSystemStat::MAIN): static
    {
        $this->stats[$type]++;

        return $this;
    }

    public function addNewStat(string|int $key = 10, string $tr = '__undefined__'): static
    {
        if ((is_int($key) && $key < 10) || !empty($this->stats[$key])) {
            return $this;
        }

        $this->stats[$key] = 0;
        $this->tr[$key] = $tr;

        return $this;
    }

    public function view(): bool
    {
        ConsoleLogger::showMessage('');
        ConsoleLogger::showMessage('----------- System Statistic -----------');

        $this->tr['memory_peak'] = 'RAM Peak';
        $this->stats['memory_peak'] = memory_get_peak_usage(true) / 1000000 . " MB";

        $this->tr['memory_curr'] = 'RAM Current';
        $this->stats['memory_curr'] = memory_get_usage(true) / 1000000 . " MB";

        foreach ($this->tr as $type => $tr) {
            ConsoleLogger::showMessage("{$tr} = {$this->stats[$type]}");

            $this->stats[$type] = 0;
        }

        // Чтобы всегда было в конце списка
        unset(
            $this->tr['memory_peak'],
            $this->tr['memory_curr']
        );

        ConsoleLogger::showMessage('----------- System Statistic -----------');
        ConsoleLogger::showMessage('');

        return true;
    }
}
