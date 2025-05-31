<?php

namespace Discord\Bot\System\Migration;

use Doctrine\DBAL\Exception;
use Vengine\Libraries\Migrations\MigrationManager as ParentMigrationManager;

class MigrationManager extends ParentMigrationManager
{
    /**
     * @throws Exception
     */
    public function runtimeCollectMigrations(): bool
    {
        $dir = ($_SERVER['base.dir'] ?? '') . '/migrations/';

        if (!is_dir($dir)) {
            return false;
        }

        $this->collectMigrationFiles($dir);

        return true;
    }
}
