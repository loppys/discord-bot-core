<?php

namespace Discord\Bot\System;

use Discord\Bot\System\GlobalRepository\Traits\LogSourceTrait;
use Discord\Bot\System\GlobalRepository\LogRepository;
use Doctrine\DBAL\Exception;

class Logger
{
    use LogSourceTrait;

    protected LogRepository $logRepository;

    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    public function write(string $value, ?string $source = null): bool
    {
        $realSource = null;
        $trace = debug_backtrace(limit: 2);

        if (!empty($trace[1]) && is_array($trace[1])) {
            $realSource = $trace[1]['object'] ?? null;
        }

        if (is_object($realSource) && method_exists($realSource, 'getLogSource')) {
            $realSource = $realSource->getLogSource();
        } else {
            $realSource = null;
        }

        return $this->logRepository->save([
            'lg_value' => $value,
            'lg_source' => $source ?? $realSource ?? $this->getLogSource()
        ]);
    }
}
