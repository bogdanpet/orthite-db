<?php


namespace Orthite\Database\Migrations;


class MysqlSchema extends Schema implements SchemaInterface
{

    public function string($column, $length = 255)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` VARCHAR(' . $length . ') NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function text($column)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` TEXT NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function binary($column)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` BLOB NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function integer($column, $size = 4)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` INT(' . $size . ') NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function double($column, $size = 4, $decimals = 2)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` DOUBLE(' . $size . ',' . $decimals . ') NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function decimal($column, $size = 4, $decimals = 2)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` DECIMAL(' . $size . ',' . $decimals . ') NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function bool($column)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` TINYINT(1) NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function date($column)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` DATE NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function datetime($column)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` DATETIME NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function timestamp($column)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` TIMESTAMP NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function time($column)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` TIME NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function year($column)
    {
        $this->pushColumn();

        $this->column = '`' . $column . '` YEAR NOT NULL';
        $this->columnName = $column;

        return $this;
    }

    public function increments($column = 'id')
    {
        $this->integer($column)->unsigned()->autoIncrement()->primary();

        return $this;
    }

    public function timestamps()
    {
        $this->timestamp('created_at');
        $this->datetime('updated_at')->nullable();
        $this->datetime('deleted_at')->nullable();

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
        $this->primaryKey = ',' . PHP_EOL .'PRIMARY KEY (`' . $this->columnName . '`)';

        return $this;
    }

    public function foreign($refTable, $refColumn)
    {
        $this->foreignKeys[] = ',' . PHP_EOL .'FOREIGN KEY (`' . $this->columnName . '`) REFERENCES `' . $refTable . '`(`' . $refColumn . '`)';

        return $this;
    }

    public function check($condition)
    {
        $this->constraints[] = 'CHECK (' . $condition . ')';

        return $this;
    }

    public function default($value)
    {
        $this->constraints[] = 'DEFAULT \'' . $value . '\'';

        return $this;
    }

    public function index()
    {
        $index = PHP_EOL .
            'CREATE INDEX idx_' . $this->columnName . ' ON `' . $this->table . '`(`' . $this->columnName . '`);';

        $this->indexes[] = $index;

        return $this;
    }

    public function unsigned()
    {
        if (strpos($this->column, 'NOT NULL') !== false) {
            $this->column = str_replace('NOT NULL', 'UNSIGNED NOT NULL', $this->column);
        } else {
            $this->column .= ' UNSIGNED';
        }

        return $this;
    }

    public function autoIncrement()
    {
        $this->constraints[] = 'AUTO_INCREMENT';

        return $this;
    }
}