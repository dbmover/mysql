<?php

namespace Dbmover\Mysql;

use PDO;

trait TableHelper
{
    /**
     * MySQL-specific implementation of getTableDefinition.
     *
     * @param string $name The name of the table.
     * @return array A hash of columns, where the key is also the column name.
     */
    public function getTableDefinition($name)
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                COLUMN_NAME colname,
                COLUMN_DEFAULT def,
                IS_NULLABLE nullable,
                DATA_TYPE coltype,
                (EXTRA = 'auto_increment') is_serial
            FROM INFORMATION_SCHEMA.COLUMNS
                WHERE (TABLE_CATALOG = ? OR TABLE_SCHEMA = ?) AND TABLE_NAME = ?
                ORDER BY ORDINAL_POSITION ASC"
        );
        $stmt->execute([$this->database, $this->database, $name]);
        $cols = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
            if (is_null($column['def']) && $column['nullable'] == 'YES') {
                $column['def'] = 'NULL';
            } elseif (!is_null($column['def'])) {
                $column['def'] = $this->pdo->quote($column['def']);
            } else {
                $column['def'] = '';
            }
            $cols[$column['colname']] = $column;
        }
        return $cols;
    }
}

