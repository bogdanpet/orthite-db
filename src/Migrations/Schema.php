<?php


namespace Orthite\Database\Migrations;


abstract class Schema
{

    /**
     * Holds current building table.
     *
     * @var string
     */
    protected $table = '';

    /**
     * Holds final table creating query.
     *
     * @var string
     */
    protected $query = '';

    /**
     * Holds current building column.
     *
     * @var string
     */
    protected $column = '';

    /**
     * Holds current building column name.
     *
     * @var string
     */
    protected $columnName = '';

    /**
     * Holds columns to be created.
     *
     * @var string
     */
    protected $columns = [];

    /**
     * Holds constraints for current building column.
     *
     * @var array
     */
    protected $constraints = [];

    /**
     * Holds primary key constraint.
     *
     * @var string
     */
    protected $primaryKey = '';

    /**
     * Holds foreign key constraints.
     *
     * @var array
     */
    protected $foreignKeys = [];

    /**
     * Holds indexes.
     *
     * @var array
     */
    protected $indexes = [];

    /**
     * Table setter.
     *
     * @param mixed $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * Prepare and push column query part to columns array.
     * Resets constraints.
     */
    protected function pushColumn()
    {
        if (!empty($this->column)) {
            $this->columns[] = $this->column . ' ' . implode(' ', $this->constraints);
        }

        $this->constraints = [];
    }

    /**
     * Build final query.
     *
     * @return string
     */
    public function build()
    {
        $this->pushColumn();

        $this->query = 'CREATE TABLE `' . $this->table . '` (' . PHP_EOL;
        $this->query .= implode(',' . PHP_EOL, $this->columns);
        $this->query .= $this->primaryKey;
        $this->query .= implode('', $this->foreignKeys). PHP_EOL;
        $this->query .= ') CHARACTER SET #$CHARSET$# COLLATE #$COLLATION$#;';
        $this->query .= implode('', $this->indexes);

        return $this->query;
    }
}