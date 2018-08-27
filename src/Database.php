<?php

namespace Orthite\Database;

class Database
{
    use CrudOperations;
    use WhereConditions;
    use Joins;

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
     * Holds the ORDER BY part of the query.
     *
     * @var string
     */
    protected $order = '';

    /**
     * Holds the GROUP BY part of the query.
     *
     * @var string
     */
    protected $group = '';

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

        $this->reset();

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
     * Generates ORDER BY part of the query.
     *
     * @param $columns
     */
    public function orderBy($columns)
    {
        if (is_string($columns)) {
            $columns = ['`' . $columns . '`'];
        }

        $this->order = 'ORDER BY ' . implode(', ', $columns);
    }

    /**
     * Generates GROUP BY part of the query.
     *
     * @param $columns
     */
    public function groupBy($columns)
    {
        if (is_string($columns)) {
            $columns = ['`' . $columns . '`'];
        }

        $this->group = 'GROUP BY ' . implode(', ', $columns);
    }

    /**
     * Reset state after successful execution.
     */
    protected function reset()
    {
        $this->where = '';
        $this->whereParams = [];
        $this->group = '';
        $this->order = '';
        $this->joins = [];
        $this->increments = [];
    }
}