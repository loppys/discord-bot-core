<?php

namespace App\System\Repository;

use App\Repository\Entity\AbstractEntity;
use Discord\Bot\System\Interfaces\RepositoryInterface;
use Discord\Bot\System\Traits\EntityCreatorTrait;
use Discord\Bot\System\DBAL;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

abstract class AbstractRepository implements RepositoryInterface
{
    use EntityCreatorTrait;

    protected string $table = '';

    protected string $primaryKey = 'id';

    protected array $columnMap = [
        'undefined'
    ];

    protected string $entityClass = '';

    protected Connection $connection;

    protected DBAL $db;

    public function __construct(DBAL $db)
    {
        $this->connection = $db->getConnection();
        $this->db = $db;
    }

    /**
     * @throws Exception
     */
    public function createEntity(string $dataKey, string $column = ''): mixed
    {
        if (!class_exists($this->entityClass)) {
            return null;
        }

        $entity = new $this->entityClass();

        if (!$entity instanceof AbstractEntity) {
            return null;
        }

        if (empty($column)) {
            $column = $this->primaryKey;
        }

        $data = $this->get([$column => $dataKey], 1);

        if (empty($data)) {
            return $entity;
        }

        return $entity->setEntityData($data);
    }

    /**
     * @throws Exception
     */
    public function save(array $data): bool
    {
        foreach ($data as $col => $val) {
            if (!in_array($col, $this->columnMap, true)) {
                unset($data[$col]);
            }
        }

        return (bool)$this->connection->createQueryBuilder()
            ->insert($this->table)
            ->values($this->db->escapeValue($data))
            ->executeStatement()
            ;
    }

    /**
     * @throws Exception
     */
    public function getRow(mixed $id): array|bool
    {
        return $this->get([$this->primaryKey => $id]);
    }

    /**
     * @throws Exception
     */
    public function getLastInsertId(): string|int
    {
        return $this->connection->lastInsertId();
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data = [], ?array $criteria = null): bool
    {
        if (empty($data)) {
            return false;
        }

        if ($criteria === null) {
            $criteria = ['id' => $id];
        }

        return (bool)$this->connection->update($this->table, $data, $criteria);
    }

    /**
     * @throws Exception
     */
    public function has(array $criteria = []): bool
    {
        if (empty($criteria)) {
            return false;
        }

        return !empty($this->get($criteria));
    }

    /**
     * @throws Exception
     */
    public function dropTable(): void
    {
        $this->connection->createSchemaManager()->dropTable($this->table);
    }

    /**
     * @throws Exception
     */
    public function getAll(): array
    {
        return $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->executeQuery()
            ->fetchAllAssociative()
            ;
    }

    /**
     * @throws Exception
     */
    public function delete(array $criteria = []): bool
    {
        if (empty($criteria)) {
            return false;
        }

        return (bool)$this->connection->delete($this->table, $criteria);
    }

    /**
     * @throws Exception
     */
    protected function get(array $criteria = [], ?int $limit = null): array|bool
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->table)
        ;

        foreach ($criteria as $k => $v) {
            if (array_key_first($criteria) === $k) {
                $qb->where($qb->expr()->eq($k, $this->db->escapeValue($v)));

                continue;
            }

            $qb->andWhere($qb->expr()->eq($k, $this->db->escapeValue($v)));
        }

        $qb->setMaxResults($limit);

        if ($limit === 1) {
            return $qb->executeQuery()->fetchAssociative();
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }
}
