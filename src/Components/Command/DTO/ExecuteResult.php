<?php

namespace Discord\Bot\Components\Command\DTO;

class ExecuteResult
{
    protected string $message = '';

    protected string $code = '0';

    protected bool $success = false;

    public static function create(string $message, string $code = '', ?bool $success = null): static
    {
        return (new static())->setMessage($message)->setCode($code)->setSuccess($success);
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): static
    {
        $this->success = $success;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
