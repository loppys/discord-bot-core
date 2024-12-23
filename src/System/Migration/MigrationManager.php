<?php

namespace Discord\Bot\System\Migration;

use Discord\Bot\System\DBAL;
use Discord\Bot\System\Helpers\ConsoleLogger;
use Discord\Bot\System\Migration\Entity\MigrationResult;
use Discord\Bot\System\Migration\Parts\Migration;
use Discord\Bot\System\Migration\Parts\MigrationQuery;
use Discord\Bot\System\Migration\Repository\MigrationRepository;
use Discord\Bot\System\Migration\Storage\MigrationTypeStorage;
use Discord\Bot\System\Repository\DTO\Query;
use Discord\Bot\System\Storages\TypeSystemStat;
use Discord\Bot\System\Traits\SystemStatAccessTrait;
use Doctrine\DBAL\Exception;
use RuntimeException;

class MigrationManager
{
    use SystemStatAccessTrait;
    use SystemStatAccessTrait;

    protected DBAL $db;

    protected MigrationRepository $repository;

    /**
     * @var array<MigrationQuery>
     */
    protected array $queryList = [];

    /**
     * @throws Exception
     */
    public function __construct(MigrationRepository $repository, DBAL $db)
    {
        ConsoleLogger::showMessage('create Migration Manager');

        $this->repository = $repository;
        $this->db = $db;

        $ds = DIRECTORY_SEPARATOR;

        $installDir = $_SERVER['core.dir'] . "{$ds}Migrations{$ds}install{$ds}";

        if (is_dir($installDir)) {
            $this->collectMigrationFiles($installDir, $this->repository->hasTable());

            $this->run();
        }
    }

    /**
     * @throws Exception
     */
    public function run(): bool
    {
        foreach ($this->queryList as $query) {
            if (!$query instanceof MigrationQuery) {
                continue;
            }

            if ($this->migrationExecute($query)) {
                $this->removeMigrationByQuery($query);
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function collectMigrationFiles(string $dirPath = '', bool $checkHash = true, bool $force = false): bool
    {
        if (!is_dir($dirPath)) {
            return false;
        }

        $dir = scandir($dirPath);

        if (!$dir) {
            return false;
        }

        $failCountFile = 0;
        foreach ($dir as $file) {
            if (!is_file($dirPath . $file)) {
                continue;
            }

            $query = $this->createMigrationQuery($dirPath . $file, $checkHash);

            if ($query === null) {
                $failCountFile++;

                continue;
            }

            if ($force) {
                if ($this->migrationExecute($query)) {
                    $this->removeMigrationByQuery($query);
                }
            }
        }

        return $failCountFile === 0;
    }

    /**
     * @throws Exception
     */
    public function createMigrationQuery(string $queryLink, bool $checkHash = true): null|MigrationQuery
    {
        if (!file_exists($queryLink)) {
            return null;
        }

        $fileHash = sha1_file($queryLink);

        if ($checkHash && (!empty($this->queryList[$fileHash]) || $this->repository->has(['mig_hash' => $fileHash]))) {
            return null;
        }

        $query = (new MigrationQuery($queryLink))->setFileHash($fileHash);

        if (pathinfo($queryLink, PATHINFO_EXTENSION) === 'sql') {
            $sql = file_get_contents($queryLink);

            if (empty($sql)) {
                return null;
            }

            $query = $query->setSqlQuery($sql);

            $this->addMigrationQuery($query);

            return $query;
        }

        if (pathinfo($queryLink, PATHINFO_EXTENSION) === 'php') {
            $php = require($queryLink);

            if (!$php instanceof Migration) {
                if (is_array($php) && !empty($php['migrationClass']) && class_exists($php['migrationClass'])) {
                    $obj = new $php['migrationClass'];

                    if (!$obj instanceof Migration) {
                        return null;
                    }

                    return $query->setPhpMigration($obj);
                }

                return null;
            }

            $query = $query->setPhpMigration($php);

            $this->addMigrationQuery($query);

            return $query;
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function migrationExecute(MigrationQuery $query): bool
    {
        if ($this->repository->hasTable() && $this->repository->has(['mig_hash' => $query->getFileHash()])) {
            return true;
        }

        if ($query->getType() === MigrationTypeStorage::NONE || empty($query->getMigrationFile())) {
            return false;
        }

        if ($query->getType() === MigrationTypeStorage::AUTO) {
            $query = $this->reCreateMigrationQuery($query->getMigrationFile());

            if ($query === null) {
                return false;
            }
        }

        ConsoleLogger::showMessage("execute migration: {$query->getFileHash()}");

        $this->getSystemStat()->add(TypeSystemStat::MIGRATIONS);

        if ($query->getType() === MigrationTypeStorage::PHP) {
            $query->getPhpMigration()->up();

            $result = $this->repository->createEntityByArray([
                'mig_file' => $query->getMigrationFile(),
                'mig_hash' => $query->getFileHash(),
                'mig_query' => 'php_migration'
            ]);
        } else {
            $sqlQuery = $query->getSqlQuery();

            $this->getSystemStat()->add(TypeSystemStat::DB);

            try {
                $sqlQueryResult = $this->db->getConnection()->executeStatement($query->getSqlQuery());
            } catch (Exception $e) {
                $msg = 'SQL Migration fail: ' . $e->getMessage();

                ConsoleLogger::showMessage($msg);

                $this->removeMigrationByQuery($query);

                $result = $this->repository->createEntityByArray([
                    'mig_file' => $query->getMigrationFile(),
                    'mig_hash' => $query->getFileHash(),
                    'mig_query' => $msg
                ]);

                $this->repository->saveByEntity($result);

                return false;
            }

            $result = $this->repository->createEntityByArray([
                'mig_file' => $query->getMigrationFile(),
                'mig_hash' => $query->getFileHash(),
                'mig_query' => "{$sqlQuery} ==> {$sqlQueryResult}"
            ]);
        }

        return $this->repository->saveByEntity($result);
    }

    public function addMigrationQuery(MigrationQuery $query): static
    {
        if (!empty($this->queryList[$query->getFileHash()])) {
            return $this;
        }

        $this->queryList[$query->getFileHash()] = $query;

        ConsoleLogger::showMessage("add migration: {$query->getFileHash()}");

        $this->getSystemStat()->add(TypeSystemStat::MIGRATIONS);

        return $this;
    }

    public function removeMigrationByHash(string $hash): static
    {
        unset($this->queryList[$hash]);

        ConsoleLogger::showMessage("remove migration: {$hash}");

        return $this;
    }

    public function removeMigrationByQuery(MigrationQuery $query): static
    {
        return $this->removeMigrationByHash($query->getFileHash());
    }

    /**
     * @throws Exception
     */
    private function reCreateMigrationQuery(string $queryLink): null|MigrationQuery
    {
        return $this->createMigrationQuery($queryLink);
    }

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
