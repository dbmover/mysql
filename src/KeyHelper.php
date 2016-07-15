<?php

namespace Dbmover\Mysql;

trait KeyHelper
{
    /**
     * Checks whether a column is an auto_increment column.
     *
     * @param string $column The referenced column definition.
     * @return bool
     */
    public function isSerial($column)
    {
        if (strpos($column->sql, 'AUTO_INCREMENT') !== false) {
            $column->sql = str_replace('AUTO_INCREMENT', '', $column->sql);
            return true;
        }
        return false;
    }

    /**
     * Checks whether a column is a primary key.
     *
     * @param string $column The referenced column definition.
     * @return bool
     */
    public function isPrimaryKey($column)
    {
        if (strpos($column->sql, 'PRIMARY KEY')) {
            $column->sql = str_replace('PRIMARY KEY', '', $column->sql);
            return true;
        }
        return false;
    }
}

