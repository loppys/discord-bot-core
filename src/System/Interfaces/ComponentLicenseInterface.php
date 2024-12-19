<?php

namespace Discord\Bot\System\Interfaces;

interface ComponentLicenseInterface
{
    public function baseActivateComponent(string $guild = ''): void;

    public function keyRequired(): bool;

    public function guildKeyValid(string $guild = ''): bool;

    public function getComponentName(): string;
}
