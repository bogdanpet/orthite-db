<?php

namespace Orthite\Database;

trait CrudOperations
{
    /**
     * Main table on which crud operations are taken.
     * Necessary for joins (will be taken as left table in join).
     *
     * @var string
     */
    protected $mainTable = '';

    /**
     * Inserts record in database table.
     *
     * @param string $table
     * @param array $data
     * @return bool|\PDOStatement
     */
    public function insert($table, array $data)
    {
        $columns = [];
        $placeholders = [];
        $params = [];

        foreach ($data as $column => $value) {

            if (!array_key_exists($column, $this->increments)) {
                $this->increments[$column] = 1;
            }

            if (!is_int($column)) {
                $columns[] = '`' . $column . '`';
            }

            $placeholder = ':' . $column . $this->increments[$column];
            $this->increments[$column]++;

            $placeholders[] = $placeholder;

            $params[$placeholder] = $value;
        }

        if (!empty($columns)) {
            $columns = '(' . implode(', ', $columns) . ')';
        } else {
            $columns = null;
        }

        $placeholders = implode(', ', $placeholders);

        $query = "INSERT INTO `$table` $columns VALUES ($placeholders)";

        return $this->execute($query, $params);
    }

    /**
     * Inserts multiple rows into database table.
     * Returns the number of successfully inserted rows.
     *
     * @param string $table
     * @param array $data
     * @return int
     */
    public function insertMany($table, array $data)
    {
        $success = 0;

        foreach ($data as $record) {
            if ($this->insert($table, $record)) {
                $success++;
            }
        }

        return $success;
    }

    /**
     * Retrieves records from database table.
     *
     * @param string $table
     * @param string $columns
     * @param int $style
     * @return array
     */
    public function select($table = null, $columns = '*', $style = \PDO::FETCH_ASSOC)
    {
        if ($table == null) {
            $table = $this->mainTable;
        } else {
            $this->mainTable = $table;
        }

        if (is_array($columns)) {
            $columns = array_map(function ($col) {
                return '`' . $col . '`';
            }, $columns);
            $columns = implode(', ', $columns);
        }

        $joins = implode(' ', $this->joins);

        $query = "SELECT $columns FROM `$table` $joins $this->where $this->group $this->order $this->limit";

        $stmt = $this->execute($query, $this->whereParams);

        return $stmt->fetchAll($style);
    }

    /**
     * Updates records in database table.
     *
     * @param string $table
     * @param array $data
     * @return bool|\PDOStatement
     */
    public function update($table, array $data)
    {
        $set = [];
        $params = [];

        foreach ($data as $column => $value) {
            if (!array_key_exists($column, $this->increments)) {
                $this->increments[$column] = 1;
            }

            $placeholder = ':' . $column . $this->increments[$column];
            $set[] = '`' . $column . '` = ' . $placeholder;
            $params[$placeholder] = $value;
        }

        $set = implode(',', $set);

        $query = "UPDATE `$table` SET $set $this->where";

        return $this->execute($query, array_merge($params, $this->whereParams));
    }

    /**
     * Deletes records from database table.
     *
     * @param $table
     * @return bool|\PDOStatement
     */
    public function delete($table)
    {
        $query = "DELETE FROM `$table` $this->where";

        return $this->execute($query, $this->whereParams);
    }

    /**
     * Main table setter.
     *
     * @param $table
     */
    public function table($table)
    {
        $this->mainTable = $table;
    }
}