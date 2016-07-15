<?php

namespace Dbmover\Mysql;

trait ProcedureWrapper
{
    /**
     * MySQL-specific `IF` wrapper.
     *
     * @param string $sql The SQL to wrap.
     * @return string The input SQL wrapped and called.
     */
    public function wrapInProcedure($sql)
    {
        $tmp = 'tmp_'.md5(microtime(true));
        return <<<EOT
DROP PROCEDURE IF EXISTS $tmp;
CREATE PROCEDURE $tmp()
BEGIN
    $sql
END;
CALL $tmp();
DROP PROCEDURE $tmp();
EOT;
    }
}

