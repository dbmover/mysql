<?php

/**
 * @package Dbmover
 * @subpackage Mysql
 */

namespace Dbmover\Mysql;

use Dbmover\Core;

/**
 * Gather all conditionals and optionally wrap them in a "lambda".
 */
class Conditionals extends Core\Conditionals
{
    protected function wrap(string $sql) : string
    {
        $database = $this->loader->getDatabase();
        $tmp = 'tmp_'.md5(microtime(true));
        return <<<EOT
DROP PROCEDURE IF EXISTS $tmp;
CREATE PROCEDURE $tmp()
BEGIN
    SET DBMOVER_DATABASE = '$database';
    $sql
END;
CALL $tmp();
DROP PROCEDURE $tmp;

EOT;
    }
}

