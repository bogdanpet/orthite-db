<?php


namespace Orthite\Database\Migrations;


abstract class Schema
{
    protected $table;

    public $query = '';

    protected $column = '';

    protected $columnName = '';

    protected $columns = [];

    protected $constraints = [];

    protected $primaryKey = '';

    protected $foreignKeys = [];

    /**
     * Table setter.
     *
     * @param mixed $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    protected function pushColumn()
    {
        if (!empty($this->column)) {
            $this->columns[] = $this->column . ' ' . implode(' ', $this->constraints);
        }

        $this->constraints = [];
    }

    public function build()
    {
        $this->pushColumn();

        $this->query = 'CREATE TABLE `' . $this->table . '` (' . PHP_EOL;
        $this->query .= implode(',' . PHP_EOL, $this->columns);
        $this->query .= $this->primaryKey;
        $this->query .= implode('', $this->foreignKeys). PHP_EOL;
        $this->query .= ') CHARACTER SET #$CHARSET$# COLLATE #$COLLATION$#;';
    }
}