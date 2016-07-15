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
    public function isSerial(&$column)
    {
        if (strpos($column, 'AUTO_INCREMENT') !== false) {
            $column = str_replace('AUTO_INCREMENT', '', $column);
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
    public function isPrimaryKey(&$column)
    {
        if (strpos($column, 'PRIMARY KEY')) {
            $column = str_replace('PRIMARY KEY', '', $column);
            return true;
        }
        return false;
    }
}

