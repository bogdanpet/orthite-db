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
     */
    protected function addJoin($table, $leftColumn, $rightColumn = null, $type = 'INNER')
    {
        $this->join = $type . ' JOIN `' . $table . '` ON `' . $this->mainTable . '.' . $leftColumn . '` = ';
        $this->join .= '`' . $table . '.' . $rightColumn !== null ? $rightColumn : $leftColumn . '`';

        $this->joins[] = $this->join;
        $this->join = '';
    }

    /**
     * Creates inner join.
     * If right column is null it tries to join table on same column name as in left table.
     *
     * @param string $table
     * @param string $leftColumn
     * @param null|string $rightColumn
     */
    public function innerJoin($table, $leftColumn, $rightColumn = null)
    {
        $this->addJoin($table, $leftColumn, $rightColumn);
    }

    /**
     * Inner join alias.
     * If right column is null it tries to join table on same column name as in left table.
     *
     * @param string $table
     * @param string $leftColumn
     * @param null|string $rightColumn
     */
    public function join($table, $leftColumn, $rightColumn)
    {
        $this->innerJoin($table, $leftColumn, $rightColumn);
    }

    /**
     * Creates left join.
     * If right column is null it tries to join table on same column name as in left table.
     *
     * @param string $table
     * @param string $leftColumn
     * @param null|string $rightColumn
     */
    public function leftJoin($table, $leftColumn, $rightColumn = null)
    {
        $this->addJoin($table, $leftColumn, $rightColumn, 'LEFT');
    }

    /**
     * Creates right join.
     * If right column is null it tries to join table on same column name as in left table.
     *
     * @param string $table
     * @param string $leftColumn
     * @param null|string $rightColumn
     */
    public function rightJoin($table, $leftColumn, $rightColumn = null)
    {
        $this->addJoin($table, $leftColumn, $rightColumn, 'RIGHT');
    }

    /**
     * Creates full join.
     * If right column is null it tries to join table on same column name as in left table.
     *
     * @param string $table
     * @param string $leftColumn
     * @param null|string $rightColumn
     */
    public function fullJoin($table, $leftColumn, $rightColumn = null)
    {
        $this->addJoin($table, $leftColumn, $rightColumn, 'FULL OUTER');
    }
}