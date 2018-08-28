<?php


namespace Orthite\Database\Migrations;


class MysqlSchema extends Schema implements SchemaInterface
{

    public function string($column, $length = 255)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` VARCHAR(' . $length . ') NOT NULL';

        return $this;
    }

    public function integer($column, $size = 4)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` INT(' . $size . ') NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function nullable()
    {
        $this->column = str_replace(' NOT NULL', '', $this->column);

        return $this;
    }

    public function unique()
    {
        $this->constraints[] = 'UNIQUE';

        return $this;
    }

    public function primary()
    {
        $this->constraints[] = 'PRIMARY KEY';

        return $this;
    }

    public function foreign($refTable, $refColumn)
    {
        $this->foreignKeys[] = ',' . PHP_EOL .'FOREIGN KEY (' . $this->columnName . ') REFERENCES ' . $refTable . '(' . $refColumn . ')';

        return $this;
    }

    public function check()
    {
        // TODO: Implement check() method.
    }

    public function default($value)
    {
        $this->constraints[] = 'DEFAULT \'' . $value . '\'';

        return $this;
    }

    public function index()
    {
        // TODO: Implement index() method.
    }

    public function unsigned()
    {
        $this->constraints[] = 'UNSIGNED';

        return $this;
    }

    public function autoIncrement()
    {
        $this->constraints[] = 'AUTO INCREMENT';

        return $this;
    }
}