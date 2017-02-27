<?php

namespace Dbmover\Mysql;

use Dbmover\Dbmover;
use PDO;

class Schema extends Dbmover\Schema implements Dbmover\Regexes, Routines
{
    use Helper\Procedure;

    /**
     * Process the schemas, wrapped for MySQL.
     */
    public function processSchemas()
    {
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        parent::processSchemas();
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    /**
     * Wrap an object name for MySQL.
     *
     * @param string $name The name to wrap.
     * @return string A wrapped name.
     */
    protected function wrapName($name)
    {
        return "`$name`";
    }

    /**
     * Unwrap an object name for MySQL.
     *
     * @param string $name The name to unwrap.
     * @return string An unwrapped name.
     */
    protected function unwrapName($name)
    {
        return preg_replace("@^`(.*?)`$@", '\\1', $name);
    }
}

