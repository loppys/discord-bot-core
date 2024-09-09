<?php

namespace Discord\Bot\System\Migration\Parts;

use Discord\Bot\Core;
use Discord\Bot\System\DBAL;
use Discord\Bot\System\Migration\Entity\MigrationResult;

abstract class Migration
{
    protected DBAL $db;

    public function __construct()
    {
        $this->db = Core::getInstance()->db;
    }

    abstract public function up(): void;
}
