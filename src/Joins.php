<?php


namespace Orthite\Database;


trait Joins
{

    /**
     * Holds a join condition.
     *
     * @var string
     */
    protected $join = '';

    /**
     * Holds all joins.
     *
     * @var array
     */
    protected $joins = [];

    /**
     * Creates the join.
     *
     * @param string $table
     * @param string $leftColumn
     * @param null|string $rightColumn
     * @param string $type
     *
     * @return $this
     */
    protected function addJoin($table, $leftColumn, $rightColumn = null, $type = 'INNER')
    {
        $this->join = $type . ' JOIN `' . $table . '` ON `' . '#$MAINTABLE$#' . '`.`' . $leftColumn . '` = ';
        $this->join .= '`' . $table . '`.`' . ($rightColumn !== null ? $rightColumn : $leftColumn) . '`';

        $this->joins[] = $this->join;
        $this->join = '';

        return $this;
    }

    /**
     * Creates inner join.
     * If right column is null it tries to join table on same column name as in left table.
     *
     * @param string $table
     * @param string $leftColumn
     * @param null|string $rightColumn
     *
     * @return $this
     */
    public function innerJoin($table, $leftColumn, $rightColumn = null)
    {
        return $this->addJoin($table, $leftColumn, $rightColumn);
    }

    /**
     * Inner join alias.
     * If right column is null it tries to join table on same column name as in left table.
     *
     * @param string $table
     * @param string $leftColumn
     * @param null|string $rightColumn
     *
     * @return $this
     */
    public function join($table, $leftColumn, $rightColumn = null)
    {
        return $this->innerJoin($table, $leftColumn, $rightColumn);
    }

    /**
     * Creates left join.
     * If right column is null it tries to join table on same column name as in left table.
     *
     * @param string $table
     * @param string $leftColumn
     * @param null|string $rightColumn
     *
     * @return $this
     */
    public function leftJoin($table, $leftColumn, $rightColumn = null)
    {
        return $this->addJoin($table, $leftColumn, $rightColumn, 'LEFT');
    }

    /**
     * Creates right join.
     * If right column is null it tries to join table on same column name as in left table.
     *
     * @param string $table
     * @param string $leftColumn
     * @param null|string $rightColumn
     *
     * @return $this
     */
    public function rightJoin($table, $leftColumn, $rightColumn = null)
    {
        return $this->addJoin($table, $leftColumn, $rightColumn, 'RIGHT');
    }

    /**
     * Creates full join.
     * If right column is null it tries to join table on same column name as in left table.
     *
     * @param string $table
     * @param string $leftColumn
     * @param null|string $rightColumn
     *
     * @return $this
     */
    public function fullJoin($table, $leftColumn, $rightColumn = null)
    {
        return $this->addJoin($table, $leftColumn, $rightColumn, 'FULL OUTER');
    }
}