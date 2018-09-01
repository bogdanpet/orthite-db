<?php


namespace Orthite\Database;


trait WhereConditions
{

    /**
     * Main WHERE method.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @param string $comparator
     * @param string $concat
     * @return $this
     */
    public function where($column, $value, $table = '#$MAINTABLE$#', $comparator = '=', $concat = 'WHERE')
    {
        if (!array_key_exists($column, $this->increments)) {
            $this->increments[$column] = 1;
        }

        $placeholder = ':' . $column . $this->increments[$column];
        $this->increments[$column]++;

        $this->where .= " $concat `$table`.`$column` $comparator $placeholder";
        $this->whereParams[$placeholder] = $value;

        return $this;
    }

    /**
     * Wrapper for where() with 'AND' concatenation.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @param string $comparator
     * @return $this
     */
    public function and($column, $value, $table = '#$MAINTABLE$#', $comparator = '=')
    {
        return $this->where($column, $value, $table, $comparator, 'AND');
    }

    /**
     * Wrapper for where() with 'OR' concatenation.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @param string $comparator
     * @return $this
     */
    public function or($column, $value, $table = '#$MAINTABLE$#', $comparator = '=')
    {
        return $this->where($column, $value, $table, $comparator, 'OR');
    }

    /**
     * Wrapper for where() with '>' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function whereGreaterThan($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->where($column, $value, $table, '>');
    }

    /**
     * Wrapper for and() with '>' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function andGreaterThan($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->and($column, $value, $table, '>');
    }

    /**
     * Wrapper for or() with '>' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function orGreaterThan($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->or($column, $value, $table, '>');
    }

    /**
     * Wrapper for where() with '<' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function whereLessThan($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->where($column, $value, $table, '<');
    }

    /**
     * Wrapper for and() with '<' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function andLessThan($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->and($column, $value, $table, '<');
    }

    /**
     * Wrapper for or() with '<' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function orLessThan($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->or($column, $value, $table, '<');
    }

    /**
     * Wrapper for where() with '>=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function whereGreaterOrEquals($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->where($column, $value, $table, '>=');
    }

    /**
     * Wrapper for and() with '>=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function andGreaterOrEquals($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->and($column, $value, $table, '>=');
    }

    /**
     * Wrapper for or() with '>=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function orGreaterOrEquals($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->or($column, $value, $table, '>=');
    }

    /**
     * Wrapper for where() with '<=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function whereLessOrEquals($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->where($column, $value, $table, '<=');
    }

    /**
     * Wrapper for and() with '<=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function andLessOrEquals($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->and($column, $value, $table, '<=');
    }

    /**
     * Wrapper for or() with '<=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function orLessOrEquals($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->or($column, $value, $table, '<=');
    }

    /**
     * Wrapper for where() with 'LIKE' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function whereLike($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->where($column, $value, $table, 'LIKE');
    }

    /**
     * Wrapper for and() with 'LIKE' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function andLike($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->and($column, $value, $table, 'LIKE');
    }

    /**
     * Wrapper for or() with 'LIKE' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @param mixed $table
     * @return $this
     */
    public function orLike($column, $value, $table = '#$MAINTABLE$#')
    {
        return $this->or($column, $value, $table, 'LIKE');
    }

    /**
     * Main WHERE IN method. Transforms array into 'IN (value1, value2, ...)'
     *
     * @param string $column
     * @param array $values
     * @param mixed $table
     * @param string $concat
     * @return $this
     */
    public function whereIn($column, $values, $table = '#$MAINTABLE$#', $concat = 'WHERE')
    {
        $placeholders = [];

        foreach ($values as $index => $value) {

            if (!array_key_exists($column, $this->increments)) {
                $this->increments[$column] = 1;
            }

            $placeholder = ':' . $column . $this->increments[$column];
            $this->increments[$column]++;

            $placeholders[$index] = $placeholder;
            $this->whereParams[$placeholder] = $value;
        }

        $this->where .= " $concat `$table`.`$column` IN (" . implode(', ', $placeholders) . ")";

        return $this;
    }

    /**
     * Wrapper for whereIN() with 'AND' concatenation.
     *
     * @param string $column
     * @param array $values
     * @param mixed $table
     * @return $this
     */
    public function andIn($column, $values, $table = '#$MAINTABLE$#')
    {
        return $this->whereIn($column, $values, $table, 'AND');
    }

    /**
     * Wrapper for whereIN() with 'OR' concatenation.
     *
     * @param string $column
     * @param array $values
     * @param mixed $table
     * @return $this
     */
    public function orIn($column, $values, $table = '#$MAINTABLE$#')
    {
        return $this->whereIn($column, $values, $table, 'OR');
    }

    /**
     * Main WHERE BETWEEN method. Generates BETWEEN value1 AND value2.
     *
     * @param string $column
     * @param mixed $value1
     * @param mixed $value2
     * @param mixed $table
     * @param string $concat
     * @return $this
     */
    public function whereBetween($column, $value1, $value2, $table = '#$MAINTABLE$#', $concat = 'WHERE')
    {

        if (!array_key_exists($column, $this->increments)) {
            $this->increments[$column] = 1;
        }

        $placeholder1 = ':' . $column . $this->increments[$column];
        $this->increments[$column]++;
        $placeholder2 = ':' . $column . $this->increments[$column];
        $this->increments[$column]++;

        $this->where .= " $concat `$column` BETWEEN $placeholder1 AND $placeholder2";
        $this->whereParams[$placeholder1] = $value1;
        $this->whereParams[$placeholder2] = $value2;

        return $this;
    }

    /**
     * Wrapper for whereBetween() with 'AND' concatenation.
     *
     * @param string $column
     * @param mixed $value1
     * @param mixed $value2
     * @param mixed $table
     * @return $this
     */
    public function andBetween($column, $value1, $value2, $table = '#$MAINTABLE$#')
    {
        return $this->whereBetween($column, $value1, $value2, $table, 'AND');
    }

    /**
     * Wrapper for whereBetween() with 'OR' concatenation.
     *
     * @param string $column
     * @param mixed $value1
     * @param mixed $value2
     * @param mixed $table
     * @return $this
     */
    public function orBetween($column, $value1, $value2, $table = '#$MAINTABLE$#')
    {
        return $this->whereBetween($column, $value1, $value2, $table, 'OR');
    }

    public function __call($name, $arguments)
    {
        $nameParts = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);

        $column = '';

        foreach ($nameParts as $index => $part) {
            if (in_array($part, ['where', 'and', 'or'])) {
                continue;
            }
            if (in_array($part, ['Greater', 'Less', 'Like', 'In', 'Between'])) {
                break;
            }

            $column .= strtolower($part) . '_';
            unset($nameParts[$index]);
        }

        $column = trim($column, '_');

        $method = implode('', $nameParts);

        return $this->$method($column, ...$arguments);
    }
}