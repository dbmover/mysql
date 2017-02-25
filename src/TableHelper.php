<?php

namespace Dbmover\Mysql;

use PDO;

trait TableHelper
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
     * MySQL-specific implementation of getTableDefinition.
     *
     * @param string $name The name of the table.
     * @return array A hash of columns, where the key is also the column name.
     */
    public function getTableDefinition($name)
    {
        $stmt = $this->pdo->prepare(sprintf(
            "SELECT
                COLUMN_NAME colname,
                COLUMN_DEFAULT def,
                IS_NULLABLE nullable,
                DATA_TYPE coltype,
                (EXTRA = 'auto_increment') is_serial
            FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_%s = ? AND TABLE_NAME = ?
                ORDER BY ORDINAL_POSITION ASC",
            static::CATALOG_COLUMN
        ));
        $stmt->execute([$this->database, $name]);
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

