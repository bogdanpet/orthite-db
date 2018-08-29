<?php


namespace Orthite\Database\Migrations;


interface SchemaInterface
{
    /*
     * Data types
     */
    public function string($column, $length = 255);
    public function text($column);
    public function binary($column);
    public function integer($column, $size = 4);
    public function double($column, $size = 4, $decimals = 2);
    public function decimal($column, $size = 4, $decimals = 2);
    public function bool($column);
    public function date($column);
    public function datetime($column);
    public function timestamp($column);
    public function time($column);
    public function year($column);

    /*
     * Combos
     */
    public function increments($column = 'id');
    public function timestamps();

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