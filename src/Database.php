<?php

namespace Orthite\Database;

class Database
{
    use CrudOperations;

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
     * Holds the WHERE condition string.
     *
     * @var string
     */
    protected $where = '';

    /**
     * Params for WHERE string.
     *
     * @var array
     */
    protected $whereParams = [];

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

    /**
     * Raw query execution.
     *
     * @param string $query
     * @return bool|\PDOStatement
     */
    public function raw($query)
    {
        return $this->pdo->query($query);
    }

    /**
     * Raw query execution with fetching data.
     * For SELECT queries only.
     *
     * @param $query
     * @param int $style
     * @return array
     */
    public function rawFetch($query, $style = \PDO::FETCH_ASSOC)
    {
        $stmt = $this->raw($query);

        return $stmt->fetchAll($style);
    }

    /**
     * Safe execution with query preparation and binding values.
     *
     * @param string $query
     * @param array $params
     * @return bool|\PDOStatement
     */
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

    /**
     * Prepare query with custom positional placeholder like '?i', '?s'.
     *
     * @param $query
     * @param $params
     * @param $positionalPlaceholders
     * @return bool|\PDOStatement
     */
    protected function preparePositionalPlaceholdersQuery($query, $params, $positionalPlaceholders)
    {
        $stmt = $this->pdo->prepare(preg_replace('/\?[i|s|b|a|l]/', '?', $query));
        foreach ($positionalPlaceholders[0] as $index => $placeholder) {
            $type = $this->findType($placeholder);
            $stmt->bindValue($index + 1, $params[$index], $type);
        }

        return $stmt;
    }

    /**
     * Prepare query with custom named placeholder like 'i:number_param', 's:string_param'.
     *
     * @param $query
     * @param $params
     * @param $namedPlaceholders
     * @return bool|\PDOStatement
     */
    protected function prepareNamedPlaceholdersQuery($query, $params, $namedPlaceholders)
    {
        $stmt = $this->pdo->prepare(preg_replace('/[i|s|b|a|l]\:/', ':', $query));
        foreach ($namedPlaceholders[0] as $placeholder) {
            $type = $this->findType($placeholder);
            $stmt->bindValue(substr($placeholder, 1), $params[substr($placeholder, 1)], $type);
        }

        return $stmt;
    }

    /**
     * Returns PDO param type according to custom placeholder.
     *
     * @param $placeholder
     * @return mixed
     */
    protected function findType($placeholder)
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

    /**
     * Generates where conditions for select, update or delete query.
     * Can be chained with mentioned methods.
     * Example: $db->where(['id', 3])->delete('users');
     *
     * @return $this
     */
    public function where()
    {
        $conditions = func_get_args();

        $where = '';

        foreach ($conditions as $index => $condition) {
            if (count($condition) == 2) {
                if ($index == 0) {
                    $where .= $condition[0] . ' = ?';
                } else {
                    $where .= ' AND ' . $condition[0] . ' = ?';
                }
                $this->whereParams[] = $condition[1];
            } else if (count($condition) == 3) {
                if (strtolower($condition[0]) == 'and' || strtolower($condition[0]) == 'or') {
                    $where .= ' ' . strtoupper($condition[0]) . ' ' . $condition[1] . ' = ?';
                } else {
                    if ($index == 0) {
                        $where .= $condition[0] . ' ' . $condition[1] . ' ?';
                    } else {
                        $where .= ' AND ' . $condition[0] . ' ' . $condition[1] . ' ?';
                    }
                }
                $this->whereParams[] = $condition[2];
            } else {
                $where .= ' ' . strtoupper($condition[0]) . ' ' . $condition[1] . ' ' . $condition[2] . ' ?';
                $this->whereParams[] = $condition[3];
            }
        }

        $this->where = ' WHERE ' . $where;

        return $this;
    }
}