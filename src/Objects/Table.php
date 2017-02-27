<?php

namespace Dbmover\Mysql\Objects;

use Dbmover\Dbmover\ObjectInterface;
use Dbmover\Dbmover\Objects;
use Dbmover\Dbmover\Helper\Ns;
use PDO;

class Table extends Objects\Table
{
    use Ns;

    protected function setCurrentIndexes(PDO $pdo, string $database)
    {
        if (!isset(self::$indexes)) {
            self::$indexes = $pdo->prepare(
                "SELECT DISTINCT INDEX_NAME name FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?"
            );
        }
        self::$indexes->execute([$database, $this->name]);
        $class = $this->getObjectName('Index');
        foreach (self::$indexes->fetchAll(PDO::FETCH_ASSOC) as $index) {
            $this->current->indexes[$index['name']] = new $class($index['name'], $this);
            $this->current->indexes[$index['name']]->setCurrentState($pdo, $database);
        }
    }
}

