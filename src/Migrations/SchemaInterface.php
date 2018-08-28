<?php


namespace Orthite\Database\Migrations;


interface SchemaInterface
{
    public function string($column, $length = 255);

    public function integer($column, $size = 4);

    /*
     * Constraints
     */
    public function nullable();
    public function unique();
    public function primary();
    public function foreign($refTable, $refColumn);
    public function check();
    public function default($value);
    public function index();
    public function unsigned();
    public function autoIncrement();
}