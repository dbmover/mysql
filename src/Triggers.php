<?php

/**
 * @package Dbmover
 * @subpackage Mysql
 */

namespace Dbmover\Mysql;

use Dbmover\Core;
use PDO;

class Triggers extends Core\Plugin
{
    /** @var string */
    const DESCRIPTION = 'Dropping existing triggers...';

    /** @var string */
    const DEFERRED = 'Recreating triggers...';

    /**
     * @param string $sql
     * @return string
     */
    public function __invoke(string $sql) : string
    {
        $stmt = $this->loader->getPdo()->prepare(
            "SELECT trigger_name, event_object_table
                FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = ?");
        $stmt->execute([$this->loader->getDatabase()]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $trigger) {
            $this->addOperation("DROP TRIGGER {$trigger['trigger_name']};");
        }
        foreach ($this->extractOperations("@^CREATE TRIGGER.*?^END;$@ms", $sql) as $trigger) {
            $this->defer($trigger[0]);
        }
        return $sql;
    }
}

