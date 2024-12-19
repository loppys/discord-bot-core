<?php

namespace Discord\Bot\Components\Command\DTO;

use Discord\Bot\Components\Command\Storage\ResultCodeStorage;

class ExecuteResult
{
    protected string $message = '';

    protected string $code = '0';

    protected bool $success = false;

    public static function create(string $code = '', ?string $customMessage = null): static
    {
        return (new static())->setCode($code, $customMessage);
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code, ?string $customMessage = null): static
    {
        $this->code = $code;

        if (in_array($code, ResultCodeStorage::SUCCESS_CODES, true)) {
            $this->success = true;

            return $this;
        }

        if ($customMessage !== null) {
            $this->message = $customMessage;
        }

        if (empty($this->message)) {
            $this->message = ResultCodeStorage::MESSAGES[$code]
                ?? ResultCodeStorage::MESSAGES[ResultCodeStorage::EMPTY_CODE];
        }

        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
