<?php


namespace Orthite\Database;


trait WhereConditions
{
    protected $increments = [];

    /**
     * Main WHERE method.
     *
     * @param string $column
     * @param mixed $value
     * @param string $comparator
     * @param string $concat
     * @return $this
     */
    public function where($column, $value, $comparator = '=', $concat = 'WHERE')
    {
        if (array_key_exists($column, $this->increments)) {
            $placeholder = ':' . $column . $this->increments[$column];
            $this->increments[$column]++;
        } else {
            $placeholder = ':' . $column;
            $this->increments[$column] = 2;
        }

        $this->where .= " $concat $column $comparator $placeholder";
        $this->whereParams[$placeholder] = $value;

        return $this;
    }

    /**
     * Wrapper for where() with 'AND' concatenation.
     *
     * @param string $column
     * @param mixed $value
     * @param string $comparator
     * @return $this
     */
    public function and($column, $value, $comparator = '=')
    {
        return $this->where($column, $value, $comparator, 'AND');
    }

    /**
     * Wrapper for where() with 'OR' concatenation.
     *
     * @param string $column
     * @param mixed $value
     * @param string $comparator
     * @return $this
     */
    public function or($column, $value, $comparator = '=')
    {
        return $this->where($column, $value, $comparator, 'OR');
    }

    /**
     * Wrapper for where() with '>' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function whereGreaterThan($column, $value)
    {
        return $this->where($column, $value, '>');
    }

    /**
     * Wrapper for and() with '>' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function andGreaterThan($column, $value)
    {
        return $this->and($column, $value, '>');
    }

    /**
     * Wrapper for or() with '>' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function orGreaterThan($column, $value)
    {
        return $this->or($column, $value, '>');
    }

    /**
     * Wrapper for where() with '<' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function whereLessThan($column, $value)
    {
        return $this->where($column, $value, '<');
    }

    /**
     * Wrapper for and() with '<' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function andLessThan($column, $value)
    {
        return $this->and($column, $value, '<');
    }

    /**
     * Wrapper for or() with '<' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function orLessThan($column, $value)
    {
        return $this->or($column, $value, '<');
    }

    /**
     * Wrapper for where() with '>=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function whereGreaterOrEquals($column, $value)
    {
        return $this->where($column, $value, '>=');
    }

    /**
     * Wrapper for and() with '>=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function andGreaterOrEquals($column, $value)
    {
        return $this->and($column, $value, '>=');
    }

    /**
     * Wrapper for or() with '>=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function orGreaterOrEquals($column, $value)
    {
        return $this->or($column, $value, '>=');
    }

    /**
     * Wrapper for where() with '<=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function whereLessOrEquals($column, $value)
    {
        return $this->where($column, $value, '<=');
    }

    /**
     * Wrapper for and() with '<=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function andLessOrEquals($column, $value)
    {
        return $this->and($column, $value, '<=');
    }

    /**
     * Wrapper for or() with '<=' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function orLessOrEquals($column, $value)
    {
        return $this->or($column, $value, '<=');
    }

    /**
     * Wrapper for where() with 'LIKE' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function whereLike($column, $value)
    {
        return $this->where($column, $value, 'LIKE');
    }

    /**
     * Wrapper for and() with 'LIKE' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function andLike($column, $value)
    {
        return $this->and($column, $value, 'LIKE');
    }

    /**
     * Wrapper for or() with 'LIKE' comparator.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function orLike($column, $value)
    {
        return $this->or($column, $value, 'LIKE');
    }

    /**
     * Main WHERE IN method. Transforms array into 'IN (value1, value2, ...)'
     *
     * @param string $column
     * @param array $values
     * @param string $concat
     * @return $this
     */
    public function whereIn($column, $values, $concat = 'WHERE')
    {
        $placeholders = [];

        foreach ($values as $index => $value) {

            if (array_key_exists($column, $this->increments)) {
                $placeholder = ':' . $column . $this->increments[$column];
                $this->increments[$column]++;
            } else {
                $placeholder = ':' . $column;
                $this->increments[$column] = 2;
            }

            $placeholders[$index] = $placeholder;
            $this->whereParams[$placeholder] = $value;
        }

        $this->where .= " $concat $column IN (" . implode(',', $placeholders) . ")";

        return $this;
    }

    /**
     * Wrapper for whereIN() with 'AND' concatenation.
     *
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function andIn($column, $values)
    {
        return $this->whereIn($column, $values, 'AND');
    }

    /**
     * Wrapper for whereIN() with 'OR' concatenation.
     *
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function orIn($column, $values)
    {
        return $this->whereIn($column, $values, 'OR');
    }

    /**
     * Main WHERE BETWEEN method. Generates BETWEEN value1 AND value2.
     *
     * @param string $column
     * @param mixed $value1
     * @param mixed $value2
     * @param string $concat
     * @return $this
     */
    public function whereBetween($column, $value1, $value2, $concat = 'WHERE')
    {
        if (array_key_exists($column, $this->increments)) {
            $placeholder1 = ':' . $column . $this->increments[$column];
            $this->increments[$column]++;
            $placeholder2 = ':' . $column . $this->increments[$column];
            $this->increments[$column]++;
        } else {
            $placeholder1 = ':' . $column;
            $placeholder2 = ':' . $column . '2';
            $this->increments[$column] = 3;
        }

        $this->where .= " $concat $column BETWEEN $placeholder1 AND $placeholder2";
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
     * @return $this
     */
    public function andBetween($column, $value1, $value2)
    {
        return $this->whereBetween($column, $value1, $value2, 'AND');
    }

    /**
     * Wrapper for whereBetween() with 'OR' concatenation.
     *
     * @param string $column
     * @param mixed $value1
     * @param mixed $value2
     * @return $this
     */
    public function orBetween($column, $value1, $value2)
    {
        return $this->whereBetween($column, $value1, $value2, 'OR');
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