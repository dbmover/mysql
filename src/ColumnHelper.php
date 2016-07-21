<?php

namespace Dbmover\Mysql;

trait ColumnHelper
{
    /**
     * MySQL-specific ALTER TABLE ... CHANGE COLUMN implementation.
     *
     * @param string $table The table to alter the column on.
     * @param array $definition Hash of desired column definition.
     * @return array An array of SQL, in this case containing a single
     *  ALTER TABLE statement.
     */
    public function alterColumn($name, array $definition)
    {
        $sql = $this->addColumn($name, $definition);
        $sql = str_replace(
            ' ADD COLUMN ',
            " CHANGE COLUMN {$definition['colname']} ",
            $sql
        ); 
        if ($definition['is_serial']) {
            $sql .= ' AUTO_INCREMENT';
        }
        return [$sql];
    }

    /**
     * Checks whether a column is an auto_increment column.
     *
     * @param StdClass $column The referenced column definition.
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
     * @param StdClass $column The referenced column definition.
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

