<?php

namespace Discord\Bot\System\Migration\Repository;

use Discord\Bot\System\Repository\AbstractRepository;
use Discord\Bot\System\Migration\Entity\MigrationResult;

class MigrationRepository extends AbstractRepository
{
    protected string $table = 'migrations';

    protected string $primaryKey = 'mig_id';

    protected array $columnMap = [
        'mig_file',
        'mig_hash',
        'mig_completed',
        'mig_query'
    ];

    protected string $entityClass = MigrationResult::class;
}