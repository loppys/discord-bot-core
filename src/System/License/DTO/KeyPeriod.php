<?php

namespace Discord\Bot\System\License\DTO;

use DateTime;

class KeyPeriod
{
    protected bool $infinity = false;

    protected ?DateTime $from = null;

    protected ?DateTime $to = null;

    public function __construct(?DateTime $from = null, ?DateTime $to = null, bool $infinity = false)
    {
        $this->infinity = $infinity;
        $this->from = $from;
        $this->to = $to;
    }

    public function getFrom(): string
    {
        if ($this->isInfinity()) {
            $this->from = new DateTime();
        }

        return $this->from->format('Y-m-d H:i:s');
    }

    public function getTo(): string
    {
        if ($this->isInfinity()) {
            $this->to = new DateTime();
        }

        return $this->to->format('Y-m-d H:i:s');
    }

    public function isInfinity(): bool
    {
        return $this->infinity;
    }

    public static function createInfinity(): static
    {
        return new static(infinity: true);
    }

    public static function createDefault(DateTime|int $from, DateTime|int $to): static
    {
        if (is_int($from)) {
            $from = (new DateTime())->setTimestamp($from);
        }

        if (is_int($to)) {
            $to = (new DateTime())->setTimestamp($to);
        }

        return new static($from, $to);
    }
}
