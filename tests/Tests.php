<?php

namespace Dbmover\Mysql\Test;

use PDO;
use Dbmover\Mysql\Schema;

/**
 * Tests for MySQL engine.
 */
class Tests
{
    public function __wakeup()
    {
        $this->pdo = new PDO(
            'mysql:dbname=dbmover_test',
            'dbmover_test',
            'moveit'
        );
        $this->pdo->exec(
            <<<EOT
DROP TABLE IF EXISTS test;
CREATE TABLE test (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bar SMALLINT NOT NULL,
    foo VARCHAR(255) DEFAULT 'buzz'
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';
DROP VIEW IF EXISTS viewtest;
EOT
        );
        putenv("DBMOVER_VENDOR=Mysql");
    }

    /**
     * Initially, `test` contains three columns {?}. After we run the migration,
     * there should be four {?}. We should also have a view called `viewtest`
     * {?} and the inserted row has '3' for `bar` since a trigger was created
     * during the migration {?}.
     */
    public function migrations(Schema &$mysql = null)
    {
        $mysql->__gentryConstruct(
            'mysql:dbname=dbmover_test',
            [
                'user' => 'dbmover_test',
                'pass' => 'moveit',
            ]
        );
        $cols = $this->pdo->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'dbmover_test'
                AND TABLE_NAME = 'test'");
        $cols->execute();
        yield assert(count($cols->fetchAll()) == 3);

        $mysql->addSchema(file_get_contents(__DIR__.'/schema.sql'));
        $mysql->processSchemas();

        $cols->execute();
        yield assert(count($cols->fetchAll()) == 4);

        $stmt = $this->pdo->prepare("SELECT * FROM viewtest");
        $stmt->execute();
        $all = $stmt->fetchAll();
        yield assert(count($all) == 1);
        yield assert($all[0]['bar'] == 3);
    }
}

