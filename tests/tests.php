<?php

use Dbmover\Core\Loader;

/**
 * Tests for MySQL engine.
 */
return function () : Generator {
    $pdo = new PDO(
        'mysql:dbname=dbmover_test',
        'dbmover_test',
        'moveit'
    );
    $pdo->exec(
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

    /**
     * Initially, `test` contains three columns. After we run the migration,
     * there should be four. We should also have a view called `viewtest`
     * and the inserted row has '3' for `bar` since a trigger was created
     * during the migration.
     */
    yield function () use (&$pdo) {
        $mysql = new Loader(
            'mysql:dbname=dbmover_test',
            [
                'user' => 'dbmover_test',
                'pass' => 'moveit',
                'schema' => ['tests/schema.sql'],
                'plugins' => ["Dbmover\\Core\\ExplicitDrop", "Dbmover\\Core\\ForceNamedIndexes", "Dbmover\\Mysql\\Plugin", "Dbmover\\Core\\Data", "Dbmover\\Mysql\\Conditionals"],
            ],
            true
        );
        $cols = $pdo->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'dbmover_test'
                AND TABLE_NAME = 'test'");
        $cols->execute();
        assert(count($cols->fetchAll()) === 3);

        $sql = $mysql->applyPlugins();
        $mysql->applyDeferred();
        $mysql->cleanup($sql);

        $cols->execute();
        assert(count($cols->fetchAll()) === 4);

        $stmt = $pdo->prepare("SELECT * FROM viewtest");
        $stmt->execute();
        $all = $stmt->fetchAll();
        assert(count($all) === 1);
        assert($all[0]['bar'] === 3);
    };
};

