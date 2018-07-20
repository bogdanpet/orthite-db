<?php

namespace Orthite\Database;

trait CrudOperations
{

    /**
     * Inserts record in database table.
     *
     * @param string $table
     * @param array $data
     * @return bool|\PDOStatement
     */
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
    public function select($table, $columns = '*', $style = \PDO::FETCH_ASSOC)
    {
        if (is_array($columns)) {
            $columns = implode(',', $columns);
        }

        $query = "SELECT $columns FROM $table $this->where";

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
            $set[] = $column . '=:' . $column;
            $params[$column] = $value;
        }

        $set = implode(',', $set);

        $query = "UPDATE $table SET $set $this->where";

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
        $query = "DELETE FROM $table $this->where";

        return $this->execute($query, $this->whereParams);
    }
}