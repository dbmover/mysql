# Dbmover\MysqlTables
MySQL-specific plugin for table migrations.

## Installation
```sh
composer require dbmover/mysql-tables
```

    This package is part of the `dbmover/mysql` metapackage, so you likely don't
    need to install it manually.

## Usage
See `dbmover/core`.

## Caveats/notes
Inline foreign key definitions aren't supported (yet). Use separate `ALTER
TABLE` statements instead.

Inline index definition support is also sketchy. For now, prefer to explicitly
define your indexes (`CREATE INDEX ...`). If you can't be bothered to type names
for all your indexes, check out the `dbmover/force-named-indexes` plugin.

## Todo
Support storage engine and collation mutations.

## Contributing
See `dbmover/core`.

