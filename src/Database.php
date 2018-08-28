<?php

namespace Orthite\Database;

use Orthite\Database\Migrations\MigrationFactory;

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
     * Database driver.
     *
     * @var string
     */
    protected $driver = 'mysql';

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
            $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
            return;
        }

        // Create connection using dsn, user, password strings
        if (is_string($args[0])) {
            try {
                $this->pdo = new \PDO(...$args);
                $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
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
            $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
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
        $query = $this->sanitizeQuery($query);

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
     *
     * @return $this
     */
    public function orderBy($columns)
    {
        if (is_string($columns)) {
            $columns = [$columns];
        }

        array_walk($columns, function(&$item) {
            $item = '`' . str_replace('|', '` ', $item);
        });

        var_dump($columns);

        $this->order = 'ORDER BY ' . implode(', ', $columns);

        return $this;
    }

    /**
     * Generates GROUP BY part of the query.
     *
     * @param $columns
     *
     * @return $this
     */
    public function groupBy($columns)
    {
        if (is_string($columns)) {
            $columns = ['`' . $columns . '`'];
        }

        $this->group = 'GROUP BY ' . implode(', ', $columns);

        return $this;
    }

    /**
     * Limits the selected results.
     *
     * @param $limit
     * @param int $chunk
     *
     * @return $this
     */
    public function limit($limit, $chunk = 1)
    {
        $concat = empty($this->where) ? 'WHERE' : 'AND';
        $limit = $concat . ' ROWNUM > ' . (($chunk - 1) * $limit) . ' AND ROWNUM <= ' . ($chunk * $limit);

        $this->where .= ' ' . $limit;

        return $this;
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

    protected function sanitizeQuery($query)
    {
        return str_replace('#$MAINTABLE$#', $this->mainTable, $query);
    }

    public function migrate($table, callable $callable) {
        $schema = MigrationFactory::create($this->driver);

        $schema->setTable($table);

        $schema = $callable($schema);

        $schema->build();

        var_dump($schema->query); die;
    }
}