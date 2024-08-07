<?php

namespace Discord\Bot\System;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Discord\Bot\Config;

class DBAL
{
    private array $param;

    private static string $_currentConnection = 'default';

    /**
     * @var array<Connection>
     */
    private static array $_instances = [];

    public function __construct()
    {
        $this->param = Config::getDatabaseParams();

        if (!empty($this->param)) {
            $this->connect();
        }
    }

    public function switchConnection(string $name, bool $createIfNotExists = false, array $params = []): Connection
    {
        if ($createIfNotExists === true && empty(static::$_instances[$name])) {
            $this->createNewConnection($name, $params);
        }

        static::$_currentConnection = $name;

        return $this->getConnection($name);
    }

    private function connect(): void
    {
        $this->createNewConnection('default', $this->param);
    }

    public function createNewConnection(string $name, array $params): Connection
    {
        if (!empty(static::$_instances[$name])) {
            return static::$_instances[$name];
        }

        $type = $params['dbType'];
        $host = $params['dbHost'];
        $dbName = $params['dbName'];
        $login = $params['dbLogin'];
        $password = $params['dbPassword'];

        $connection = DriverManager::getConnection(
            [
                'dbname' => $dbName,
                'user' => $login,
                'password' => $password,
                'host' => $host,
                'driver' => $type,
            ]
        );

        static::$_currentConnection = $name;
        static::$_instances[$name] = $connection;

        return $connection;
    }

    public function getConnection(string $name = 'default'): Connection
    {
        if (empty(static::$_instances[$name])) {
            throw new \RuntimeException('connection instance not found');
        }

        return static::$_instances[$name];
    }

    public function getCurrentConnection(): string
    {
        return self::$_currentConnection;
    }

    public function escapeValue(mixed $value, bool $column = false): int|string|bool|array
    {
        if ($value === null) {
            return 0;
        }

        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->escapeValue($val);
            }

            return $value;
        }

        switch (gettype($value)) {
            case 'integer':
            case 'boolean':
                if ($column) {
                    return '';
                }

                return (int)$value;
            case 'array':
                if ($column) {
                    return '';
                }

                return serialize($value);
            default:
                if ($column) {
                    return '`' . addslashes($value) . '`';
                }

                return '"' . addslashes($value) . '"';
        }
    }
}
