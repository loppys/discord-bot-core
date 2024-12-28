<?php

namespace Discord\Bot\System\Migration\Parts;

use Discord\Bot\Core;
use Vengine\Libraries\DBAL\Adapter;
use Discord\Bot\System\Migration\Entity\MigrationResult;

abstract class Migration
{
    protected Adapter $db;

    public function __construct()
    {
        $this->db = Core::getInstance()->db;
    }

    abstract public function up(): void;
}
