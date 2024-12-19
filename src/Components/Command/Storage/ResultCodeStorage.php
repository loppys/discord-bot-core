<?php

namespace Discord\Bot\Components\Command\Storage;

class ResultCodeStorage
{
    public const EMPTY_CODE = '0';
    public const NOT_FOUND = '0x001';
    public const FAIL_CREATE_COMMAND = '0x002';
    public const FAIL_EXECUTE_COMMAND = '1x001';
    public const NOT_FORMED_CORRECTLY = '1x002';

    public const SUCCESS = '1';

    public const MESSAGES = [
        self::EMPTY_CODE => 'Неизвестная ошибка.',
        self::NOT_FOUND => 'Команда не найдена.',
        self::FAIL_CREATE_COMMAND => 'Не удалось создать команду.',
        self::FAIL_EXECUTE_COMMAND => 'Не удалось выполнить команду.',
        self::NOT_FORMED_CORRECTLY => 'Команда сформирована некорректно.',
    ];

    public const ERROR_CODES = [
        self::EMPTY_CODE,
        self::NOT_FOUND,
        self::FAIL_CREATE_COMMAND,
        self::FAIL_EXECUTE_COMMAND,
        self::NOT_FORMED_CORRECTLY,
    ];

    public const SUCCESS_CODES = [
        self::SUCCESS
    ];
}
