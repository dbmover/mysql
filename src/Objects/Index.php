<?php

namespace Dbmover\Mysql\Objects;

use Dbmover\Dbmover\ObjectInterface;
use Dbmover\Dbmover\Objects\Sql;
use Dbmover\Dbmover\Helper\Equals;
use PDO;

class Index extends Sql
{
    use Equals;

    public $name;
    public $parent;
    protected static $define;
    protected static $constraints;
    protected static $serial;
    public $current;
    public $requested;

    public function __construct(string $name, ObjectInterface $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    public function setCurrentState(PDO $pdo, string $database)
    {
        if (!isset(self::$define, self::$constraints)) {
            self::$define = $pdo->prepare(
                "SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE
                    TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?"
            );
            self::$constraints = $pdo->prepare(
                "SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE
                    CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?"
            );
            self::$serial = $pdo->prepare(
                "SELECT EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?"
            );
        }
        self::$define->execute([$database, $this->parent->name, $this->name]);
        $this->current = (object)[];
        $this->current->isPrimaryKey = $this->name == 'PRIMARY';
        $this->current->isSerial = false;
        $this->current->isUnique = false;
        $this->current->columns = [];
        foreach (self::$define->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $this->current->columns[] = $column['COLUMN_NAME'];
        }
        if ($this->name != 'PRIMARY') {
            self::$constraints->execute([$database, $this->parent->name, $this->name]);
            if (self::$constraints->fetch()) {
                $this->current->isUnique = true;
            }
        } else {
            if (count($this->current->columns) == 1) {
                self::$serial->execute([$database, $this->parent->name, $this->name]);
                $type = self::$serial->fetchColumn();
                $this->current->isSerial = strpos($type, 'auto_increment') !== false;
            }
        }
    }

    public function toSql() : array
    {
        if (!isset($this->requested)) {
            if ($this->current->isPrimaryKey) {
                return ["ALTER TABLE `{$this->parent->name}` DROP PRIMARY KEY"];
            } else {
                return ["DROP INDEX `{$this->name}` ON {$this->parent->name}"];
            }
        }
        if ($this->equals($this->current, $this->requested->current)) {
            return [];
        }
        $operations = [];
        if ($this->current) {
            if ($this->current->isPrimaryKey) {
                $operations[] = "ALTER TABLE `{$this->parent->name}` DROP PRIMARY KEY";
            } else {
                $operations[] = "DROP INDEX `{$this->name}` ON {$this->parent->name}";
            }
        }
        $operations[] = sprintf(
            "CREATE %s INDEX %s ON %s (%s)",
            $this->requested->current->isUnique ? 'UNIQUE' : '',
            $this->name,
            $this->parent->name,
            implode(', ', $this->requested->current->columns)
        );
        return $operations;
    }

    public static function fromSql(string $sql, ObjectInterface $parent = null) : ObjectInterface
    {
        if (preg_match("@^CREATE\s+(UNIQUE\s+)?INDEX\s+(`?\w+`?)?\s*ON\s+(\w+)\s*\((.*?)\);@", $sql, $matches)) {
            if (!$matches[2]) {
                $matches[2] = preg_replace('@,\s*@', '_', $matches[4]).'_idx';
            }
            $name = preg_replace('@^`(.*?)`$@', '\\1', trim($matches[2]));
            $index = new self($name, $parent);
            $index->current = (object)['isPrimaryKey' => false];
            $index->current->columns = preg_split("@,\s*@", $matches[4]);
            $index->table = $matches[3];
        } else {
            preg_match("@^ALTER TABLE\s+(\w+?)\s+ADD PRIMARY KEY\((.*?)\)@", $sql, $matches);
            $index = new self('PRIMARY', $parent);
            $index->current = (object)['isPrimaryKey' => true];
            $index->current->columns = preg_split("@,\s*@", $matches[2]);
            $index->table = $matches[1];
        }
        if ($matches[1]) {
            $index->current->isUnique = true;
        } else {
            $index->current->isUnique = false;
        }
        return $index;
    }
}

