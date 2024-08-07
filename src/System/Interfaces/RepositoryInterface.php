<?php

namespace Discord\Bot\System\Interfaces;

use Doctrine\DBAL\Exception;

interface RepositoryInterface
{
    public function createEntity(string $dataKey): mixed;

    /**
     * @throws Exception
     */
    public function save(array $data): bool;

    /**
     * @throws Exception
     */
    public function getRow(mixed $id): array|bool;

    /**
     * @throws Exception
     */
    public function getLastInsertId(): string|int;

    /**
     * @throws Exception
     */
    public function update(int $id, array $data = [], ?array $criteria = null): bool;

    /**
     * @throws Exception
     */
    public function has(array $criteria = []): bool;

    /**
     * @throws Exception
     */
    public function dropTable(): void;

    /**
     * @throws Exception
     */
    public function getAll(): array;

    /**
     * @throws Exception
     */
    public function delete(array $criteria = []): bool;
}