<?php

namespace Discord\Bot\System\Repository;

use Discord\Bot\System\Repository\DTO\DependencyTable;
use Discord\Bot\System\Repository\Entity\AbstractEntity;
use Discord\Bot\System\Interfaces\RepositoryInterface;
use Discord\Bot\System\DBAL;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractRepository implements RepositoryInterface
{
    protected string $table = '';

    protected string $primaryKey = 'id';

    protected array $columnMap = [
        'undefined'
    ];

    protected string $entityClass = '';

    /**
     * @var array<DependencyTable|array>
     */
    protected array $dependencyTableList = [];

    protected Connection $connection;

    protected DBAL $db;

    public function __construct(DBAL $db)
    {
        $this->connection = $db->getConnection();
        $this->db = $db;

        array_unshift($this->columnMap, $this->primaryKey);
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @throws Exception
     */
    public function createEntity(array $criteria = []): mixed
    {
        if (!class_exists($this->entityClass)) {
            return null;
        }

        $entity = new $this->entityClass();

        if (!$entity instanceof AbstractEntity) {
            return null;
        }

        $entity->setColumns($this->columnMap);

        $data = $this->get($criteria, 1);

        if (empty($data)) {
            return $entity;
        }

        return $entity->setEntityData($data);
    }

    public function createEntityByArray(array $data): ?AbstractEntity
    {
        if (!class_exists($this->entityClass)) {
            return null;
        }

        $entity = new $this->entityClass;

        if ($entity instanceof AbstractEntity) {
            return $entity->setColumns($this->columnMap)->setEntityData($data);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function saveByEntity(AbstractEntity $entity): bool
    {
        $data = $entity->getEntityData();

        if (empty($data)) {
            return false;
        }

        return $this->save($data);
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

            if (empty($val)) {
                unset($data[$col]);
            }

            if ($col === $this->primaryKey) {
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
    public function getRowByCriteria(array $criteria = []): array|bool
    {
        return $this->get($criteria, 1);
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
    public function updateByPrimaryKey(int $id, array $data = []): bool
    {
        return $this->update($data, [$this->primaryKey => $id]);
    }

    /**
     * @throws Exception
     */
    public function update(array $data = [], ?array $criteria = null): bool
    {
        if (empty($data)) {
            return false;
        }

        return (bool)$this->connection->update($this->table, $data, $criteria);
    }

    /**
     * @throws Exception
     */
    public function updateByEntity(AbstractEntity $entity, array $updateKeys = []): bool
    {
        if (!in_array($this->primaryKey, $entity->getColumns(), true)) {
            return false;
        }

        $id = $entity->getDataByName($this->primaryKey);

        if (empty($id)) {
            return false;
        }

        $data = [];
        if (!empty($updateKeys)) {
            foreach ($updateKeys as $key => $val) {
                $data[$key] = $val;
            }
        } else {
            $data = $entity->toArray();
        }

        return $this->updateByPrimaryKey($id, $data);
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
    public function hasTable(): bool
    {
        return $this->connection->createSchemaManager()->tableExists($this->table);
    }

    /**
     * @throws Exception
     * @return AbstractEntity[]
     */
    public function getAll(array $criteria = []): array
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

        $data = $qb->executeQuery()->fetchAllAssociative();

        $result = [];
        foreach ($data as $item) {
            $entity = $this->createEntityByArray($item);

            if ($entity !== null) {
                $result[] = $entity;
            }
        }

        return $result;
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
            ->select('t1.*')
            ->from($this->table, 't1')
        ;

        foreach ($criteria as $k => $v) {
            if (array_key_first($criteria) === $k) {
                $qb->where($qb->expr()->eq($k, $this->db->escapeValue($v)));

                continue;
            }

            $qb->andWhere($qb->expr()->eq($k, $this->db->escapeValue($v)));
        }

        $qb->setMaxResults($limit);
        
        if (!empty($this->dependencyTableList)) {
            $qb = $this->dependencyRecursiveCreate($qb, $this->dependencyTableList);
        }

        if ($limit === 1) {
            return $qb->executeQuery()->fetchAssociative();
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    private function dependencyRecursiveCreate(
        QueryBuilder $queryBuilder,
        null|array|DependencyTable $dependency = null
    ): QueryBuilder {
        if ($dependency === null) {
            return $queryBuilder;
        }

        if (is_array($dependency)) {
            foreach ($dependency as $dependencyTable) {
                if (is_array($dependencyTable)) {
                    $dependencyTable = DependencyTable::create($dependencyTable);
                }

                if (!$dependencyTable->isValid()) {
                    continue;
                }

                $queryBuilder = $this->createDependency($queryBuilder, $dependencyTable);

                if (!empty($dependencyTable->getDependencyIsDependent())) {
                    $queryBuilder = $this->dependencyRecursiveCreate($queryBuilder, $dependencyTable->getDependencyIsDependent());
                }
            }
        } else {
            if (!$dependency->isValid()) {
                return $queryBuilder;
            }

            $queryBuilder = $this->createDependency($queryBuilder, $dependency);

            if (!empty($dependency->getDependencyIsDependent())) {
                $queryBuilder = $this->dependencyRecursiveCreate($queryBuilder, $dependency->getDependencyIsDependent());
            }
        }

        return $queryBuilder;
    }

    private function createDependency(QueryBuilder $queryBuilder, DependencyTable $dependencyTable): QueryBuilder
    {
        $joinMethod = $dependencyTable->getJoinType() . 'Join';
        if (!method_exists($queryBuilder, $joinMethod)) {
            return $queryBuilder;
        }

        if (empty($dependencyTable->getConditionFromColumns())) {
            return $queryBuilder;
        }

        if (!empty($dependencyTable->getSelectColumns())) {
            foreach ($dependencyTable->getSelectColumns() as $selectColumn) {
                $queryBuilder->addSelect(
                    $dependencyTable->getAliasTable() . '.' . $selectColumn
                );
            }
        } else {
            $queryBuilder->addSelect($dependencyTable->getAliasTable() . '.*');
        }

        $conditionColumns = $dependencyTable->getConditionFromColumns();

        $condition = '';

        $firstKey = array_key_first($conditionColumns);
        foreach ($conditionColumns as $key => $column) {
            $clm = $dependencyTable->getAliasTable() . '.' . $this->db->escapeValue($key, true);
            $val = $dependencyTable->getFromAlias() . '.' . $this->db->escapeValue($column, true);

            if ($firstKey === $column) {
                $condition = $queryBuilder->expr()->eq($clm, $val);
            } else {
                $condition .= $queryBuilder->expr()->eq($clm, $val);
            }
        }

        $queryBuilder->{$joinMethod}(
            $dependencyTable->getFromAlias(),
            $dependencyTable->getFromTable(),
            $dependencyTable->getAliasTable(),
            $condition
        );

        return $queryBuilder;
    }
}
