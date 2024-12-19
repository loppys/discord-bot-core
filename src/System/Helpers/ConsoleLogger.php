<?php

namespace Discord\Bot\System\Helpers;

class ConsoleLogger
{
    public static function mixedMessage(string $message, array $dataArray = []): void
    {
        $time = date('d.m.Y H:i:s');

        if (!empty($dataArray)) {
            $dataArray = json_encode($dataArray);
            $message .= " :: {$dataArray}";
        }

        print "[{$time}] {$message}" . PHP_EOL;
    }

    public static function showMessage(string|array $message = ''): void
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }

        $time = date('d.m.Y H:i:s');

        print "[{$time}] {$message}" . PHP_EOL;
    }
}
