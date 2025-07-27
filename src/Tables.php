<?php

/**
 * @package Dbmover
 * @subpackage Mysql
 */

namespace Dbmover\Mysql;

use Dbmover\Core;
use PDO;

class Tables extends Core\Tables
{
    /**
     * @param Dbmover\Core\Loader $loader
     * @return void
     */
    public function __construct(Core\Loader $loader)
    {
        parent::__construct($loader);
        $this->columns = $this->loader->getPdo()->prepare(
            "SELECT
                CONCAT('`', column_name, '`') column_name,
                column_default,
                is_nullable,
                column_type,
                extra
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION ASC");
    }

    /**
     * @param string $sql
     * @return string
     */
    public function __invoke(string $sql) : string
    {
        $exists = $this->loader->getPdo()->prepare(
            "SELECT T.*, C.CHARACTER_SET_NAME FROM INFORMATION_SCHEMA.TABLES T
                JOIN INFORMATION_SCHEMA.COLLATION_CHARACTER_SET_APPLICABILITY C ON C.COLLATION_NAME = T.TABLE_COLLATION
                WHERE ((T.TABLE_CATALOG = ? AND T.TABLE_SCHEMA = 'public') OR T.TABLE_SCHEMA = ?)
                AND T.TABLE_TYPE = 'BASE TABLE'
                AND T.TABLE_NAME = ?");
        preg_match_all(
            "@^CREATE TABLE\s*([^\s]+)\s*\(.*?^\) ENGINE=(\w+) DEFAULT CHARSET=(\w+)(\s+COLLATE=(\w+))?;$@ms",
            $sql,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $match) {
            $exists->execute([$this->loader->getDatabase(), $this->loader->getDatabase(), $match[1]]);
            if (false !== ($table = $exists->fetch(PDO::FETCH_ASSOC))) {
                // The table exists:
                if ($table['ENGINE'] != $match[2]) {
                    $this->addOperation("ALTER TABLE `{$match[1]}` ENGINE = {$match[2]}");
                }
                if ($table['CHARACTER_SET_NAME'] != $match[3]) {
                    $this->addOperation("ALTER TABLE `{$match[1]}` CONVERT TO CHARACTER SET {$match[3]}");
                }
                if (isset($match[5]) && $match[5] && $table['TABLE_COLLATION'] != $match[5]) {
                    $this->addOperation("ALTER TABLE `{$match[1]}` COLLATE {$match[5]}");
                }
            }
        }
        $sql = preg_replace_callback(
            '@^CREATE TABLE.*\((.*?)^\)@ms',
            fn ($match) =>  preg_replace_callback(
                "@^\s*([a-z][^\s]+)@m",
                function ($match) {
                    if (!preg_match("@^`.*?`$@", $match[1]) && $match[1] != 'PRIMARY') {
                        $match[1] = "`{$match[1]}`";
                    }
                    return " {$match[1]} ";
                },
                $match[0]
            ),
            $sql
        );
        return parent::__invoke($sql);
    }

    /**
     * @param string $table
     * @param string $column
     * @param array $definition
     * @param array $current
     * @return array Array of SQL statements
     */
    protected function modifyColumn(string $table, string $column, array $definition, array $current) : array
    {
        if ($current['column_default'] ?? 'NULL' == 'NULL' || !strlen($current['column_default'])) {
            $current['column_default'] = null;
        }
        if (strtoupper($definition['column_default'] ?? 'NULL') === 'NULL') {
            $definition['column_default'] = null;
        }
        if (preg_match("@^'.*?'$@", $current['column_default'] ?? '')) {
            $current['column_default'] = substr($current['column_default'], 1, -1);
        }
        // Types will need some rewriting:
        $definition['column_type'] = preg_replace('@\s*AUTO_INCREMENT$@', '', $definition['column_type']);
        $definition['column_type'] = str_ireplace('INTEGER', 'INT', $definition['column_type']);
        $definition['column_type'] = preg_replace_callback(
            '@(TINYINT|SMALLINT|INT|MEDIUMINT|BIGINT)(?!\()@i',
            function ($match) use ($definition) {
                // Signed/unsigned integers have different lengths in MySQL
                $mod = stripos($definition['column_type'], 'UNSIGNED') ? 0 : 1;
                switch ($match[1]) {
                    case 'TINYINT': return sprintf('TINYINT(%d)', 3 + $mod);
                    case 'SMALLINT': return sprintf('SMALLINT(%d)', 5 + $mod);
                    case 'MEDIUMINT': return sprintf('MEDIUMINT(%d)', 8 + $mod);
                    case 'INT': return sprintf('INT(%d)', 10 + $mod);
                    // For reasons I don't understand, this logic does not hold for bigints...
                    case 'BIGINT': return 'BIGINT(20)';
                }
                return $match[1];
            },
            $definition['column_type']
        );
        if (preg_match('@^ENUM@i', $definition['column_type'])) {
            $definition['column_type'] = preg_replace('@,\s+@', ',', strtoupper($definition['column_type']));
        }
        if (isset($definition['column_default']) && strtoupper($definition['column_default']) == 'NOW()') {
            $definition['column_default'] = 'CURRENT_TIMESTAMP';
        }
        if (isset($current['column_default'])) {
            $current['column_default'] = preg_replace(
                '@current_timestamp\(\)@i',
                'CURRENT_TIMESTAMP',
                $current['column_default']
            );
        }
        if (preg_match('@(ON UPDATE.*|AUTO_INCREMENT)@', $definition['_definition'], $match)) {
            $definition['extra'] = $match[1];
            $definition['column_default'] = trim(str_replace($match[1], '', $definition['column_default'] ?? ''));
        } else {
            $definition['extra'] = '';
        }
        if (preg_match("@^'.*?'@", $definition['_default'] ?? '')
            && preg_match("@'$@", $definition['column_default'] ?? '')
        ) {
            $definition['column_default'] = preg_replace("@'$@", '', $definition['column_default'] ?? '');
        }

        switch (strtoupper($definition['column_type'])) {
            case 'INTEGER': $definition['column_type'] = 'INT(11)'; break;
        }
        if ((isset($definition['column_default']) && $definition['column_default'] != $current['column_default'])
            || (!isset($definition['column_default']) && strlen($current['column_default'] ?? ''))
            || strtoupper($definition['column_type']) != strtoupper($current['column_type'])
            || $definition['is_nullable'] != $current['is_nullable']
            || strtoupper($definition['extra']) != strtoupper($current['extra'])
        ) {
            return ["ALTER TABLE $table CHANGE COLUMN $column {$definition['_definition']};"];
        } else {
            return [];
        }
    }

    /**
     * @param string $table
     * @param string $sql
     * @return void
    protected function checkTableStatus(string $table, string $sql) : void
    {
        $sql = preg_replace_callback(
            "@^\s*([^\s]+)@m",
            function ($matches) {
                if (!preg_match("@^`.*?`$@", $matches[1]) && $matches[1] != 'PRIMARY') {
                    $matches[1] = "`{$matches[1]}`";
                }
                return $matches[1];
            },
            $sql
        );
        parent::checkTableStatus($table, $sql);
    }
     */
}

