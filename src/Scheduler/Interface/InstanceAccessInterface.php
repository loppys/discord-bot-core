<?php

namespace Discord\Bot\Scheduler\Interface;

interface InstanceAccessInterface
{
    public function getClass(): string;

    public function getInstance(): static;
}
