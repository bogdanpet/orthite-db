<?php


namespace Orthite\Database\Migrations;


class MigrationFactory
{
    /**
     * Returns the appropriate migration object according to driver.
     *
     * @param $driver
     *
     * @return SchemaInterface $schema
     */
    public static function create($driver)
    {
        $schema = '\\Orthite\\Database\\Migrations\\' . ucfirst($driver) . 'Schema';

        return new $schema;
    }
}