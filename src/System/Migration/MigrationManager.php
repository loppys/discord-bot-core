<?php

namespace Discord\Bot\System\Migration;

use Discord\Bot\System\DBAL;
use Discord\Bot\System\Migration\Entity\MigrationResult;
use Discord\Bot\System\Migration\Parts\Migration;
use Discord\Bot\System\Migration\Parts\MigrationQuery;
use Discord\Bot\System\Migration\Repository\MigrationRepository;
use Discord\Bot\System\Migration\Storage\MigrationTypeStorage;
use Doctrine\DBAL\Exception;
use RuntimeException;

class MigrationManager
{
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
    public function run(): void
    {
        foreach ($this->queryList as $query) {
            if (!$query instanceof MigrationQuery) {
                continue;
            }

            if ($this->migrationExecute($query)) {
                $this->removeMigrationQuery(
                    $query->getFileHash()
                );
            }
        }
    }

    /**
     * @throws Exception
     */
    public function collectMigrationFiles(string $dirPath = '', bool $checkHash = true): bool
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

            if ($query !== null) {
                $this->addMigrationQuery($query);
            } else {
                $failCountFile++;
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

        if ($checkHash && $this->repository->has(['mig_hash' => $fileHash])) {
            return null;
        }

        $query = (new MigrationQuery($queryLink))->setFileHash($fileHash);

        if (pathinfo($queryLink, PATHINFO_EXTENSION) === 'sql') {
            $sql = file_get_contents($queryLink);

            if (empty($sql)) {
                return null;
            }

            return $query->setSqlQuery($sql);
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

            return $query->setPhpMigration($php);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function migrationExecute(MigrationQuery $query): bool
    {
        if ($query->getType() === MigrationTypeStorage::NONE || empty($query->getMigrationFile())) {
            return false;
        }

        if ($query->getType() === MigrationTypeStorage::AUTO) {
            $query = $this->reCreateMigrationQuery($query->getMigrationFile());

            if ($query === null) {
                return false;
            }
        }

        if ($query->getType() === MigrationTypeStorage::PHP) {
            $result = $query->getPhpMigration()->up();

            if (empty($result->mig_file) && empty($result->mig_hash)) {
                $result->mig_file = $query->getMigrationFile();
                $result->mig_hash = $query->getFileHash();
            }
        } else {
            $result = new MigrationResult();

            $sqlQuery = $query->getSqlQuery();

            $result->mig_file = $query->getMigrationFile();
            $result->mig_hash = $query->getFileHash();
            $result->mig_query = $sqlQuery;

            $this->db->getConnection()->executeStatement($query->getSqlQuery());
        }

        return $this->repository->saveByEntity($result);
    }

    public function addMigrationQuery(MigrationQuery $query): static
    {
        $this->queryList[$query->getFileHash()] = $query;

        return $this;
    }

    public function removeMigrationQuery(string $hash): static
    {
        unset($this->queryList[$hash]);

        return $this;
    }

    /**
     * @throws Exception
     */
    private function reCreateMigrationQuery(string $queryLink): null|MigrationQuery
    {
        return $this->createMigrationQuery($queryLink);
    }
}
