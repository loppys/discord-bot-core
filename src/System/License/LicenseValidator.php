<?php

namespace Discord\Bot\System\License;

trait LicenseValidator
{
    protected bool $useLicense = false;

    public function licenseValid(string $guild = ''): bool
    {
        if (!method_exists($this, 'getComponentName')) {
            return false;
        }

        if ($key = $this->getKey($guild, $this->getComponentName())) {
            return $key->isExpired();
        }

        return false;
    }
}
