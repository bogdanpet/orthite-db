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
     * Holds connection details.
     * - driver,
     * - host,
     * - port,
     * - database,
     * - user,
     * - password
     *
     * @var array
     */
    protected $connection = [];

    /**
     * Database constructor.
     */
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
            $this->connection = array_merge($this->connection, $args[0]);
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

    public function raw($query)
    {
        return $this->pdo->query($query);
    }

    public function rawFetch($query, $style = \PDO::FETCH_ASSOC)
    {
        $stmt = $this->raw($query);

        return $stmt->fetchAll($style);
    }

    public function execute($query, array $params = [])
    {
        $hasPositionalPlaceholders = preg_match_all('/\?[i|s|b|a|l]/', $query, $positionalPlaceholders);
        $hasNamedPlaceholders = preg_match_all('/[i|s|b|a|l]\:[a-zA-Z0-9_]+/', $query, $namedPlaceholders);

        if ($hasPositionalPlaceholders) {
            $stmt = $this->preparePositionalPlaceholdersQuery($query, $params, $positionalPlaceholders);
            $stmt->execute();
        } else if ($hasNamedPlaceholders) {
            $stmt = $this->prepareNamedPlaceholdersQuery($query, $params, $namedPlaceholders);
            $stmt->execute();
        } else {
            try {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($params);
            } catch (\PDOException $e) {
                die($e->getMessage());
            }

        }

        return $stmt;
    }

    private function preparePositionalPlaceholdersQuery($query, $params, $positionalPlaceholders)
    {
        $stmt = $this->pdo->prepare(preg_replace('/\?[i|s|b|a|l]/', '?', $query));
        foreach ($positionalPlaceholders[0] as $index => $placeholder) {
            $type = $this->findType($placeholder);
            $stmt->bindValue($index + 1, $params[$index], $type);
        }

        return $stmt;
    }

    private function prepareNamedPlaceholdersQuery($query, $params, $namedPlaceholders)
    {
        $stmt = $this->pdo->prepare(preg_replace('/[i|s|b|a|l]\:/', ':', $query));
        foreach ($namedPlaceholders[0] as $placeholder) {
            $type = $this->findType($placeholder);
            $stmt->bindValue(substr($placeholder, 1), $params[substr($placeholder, 1)], $type);
        }

        return $stmt;
    }

    private function findType($placeholder)
    {
        $p = str_replace('?', '', $placeholder);
        $p = explode(':', $p)[0];

        $types = [
            'i' => \PDO::PARAM_INT,
            's' => \PDO::PARAM_STR,
            'b' => \PDO::PARAM_BOOL,
            'a' => 'array',
            'l' => \PDO::PARAM_LOB
        ];

        return $types[$p];
    }

    public function insert($table, array $data)
    {
        $columns = null;
        $placeholders = [];
        $params = [];

        foreach ($data as $column => $value) {
            if (!is_int($column)) {
                $columns = [];
                $columns[] = $column;
            }

            $placeholders[] = '?';

            $params[] = $value;
        }

        if (!empty($columns)) {
            $columns = '(' . implode(',', $columns) . ')';
        }

        $placeholders = implode(',', $placeholders);

        $query = "INSERT INTO $table $columns VALUES ($placeholders)";

        $this->execute($query, $params);
    }
}