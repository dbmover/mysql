<?php

namespace Dbmover\Mysql;

trait IndexHelper
{
    /**
     * Returns an array of [tbl, idx] hashes with index names and the tables
     * they were specified on.
     *
     * @return array Array of hashes with index information.
     */
    public function getIndexes()
    {
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT table_name AS tbl,
                index_name idx 
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE table_schema = ?"
        );
        $stmt->execute([$this->database]);
        return $stmt->fetchAll();
    }

    /**
     * Generate drop statements for all indexes in the database.
     *
     * @return array Array of SQL operations.
     */
    public function dropIndexes()
    {
        $operations = [];
        if ($indexes = $this->getIndexes()) {
            foreach ($indexes as $index) {
                $operations[] = "DROP INDEX {$index['idx']}
                    ON {$index['tbl']}";
            }
        }
        return $operations;
    }
}

