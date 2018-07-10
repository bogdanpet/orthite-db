<?php

namespace Orthite\Database;

class Database
{

    /**
     * Holds the active PDO instance.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     *
     *
     * @var array
     */
    protected $connection = [];

    public function __construct()
    {
        // Get constructor arguments
        $args = func_get_args();

        // Create connection by recycling PDO object
        if ($args[0] instanceof \PDO) {
            $this->pdo = $args[0];
            return;
        }

        // Create connection using dsn, user, password strings
        if (is_string($args[0])) {
            try {
                $this->pdo = new \PDO(...$args);
                return;
            } catch (\PDOException $e) {
                die($e->getMessage());
            }
        }

        // Create from connection array
        if (is_array($args[0])) {
            array_merge($this->connection, $args[0]);
        }
        $driver = $this->connection['driver'] ?: 'mysql';
        $dsn = $driver . ':host=' . $this->connection['host'] . ';';
        if (!empty($this->connection['port'])) {
            $dsn .= 'port=' . $this->connection['port'] . ';';
        }
        $dsn .= 'dbname=' . $this->connection['database'];

        try {
            $this->pdo = new \PDO($dsn, $this->connection['user'], $this->connection['password']);
            return;
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }
}