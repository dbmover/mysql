# Dbmover\MysqlConditionals
SQL extension to support conditionals in MySQL schemas

## Installation

```sh
$ composer require dbmover/mysql-conditionals
```

## Usage
For general DbMover usage, see `dbmover/core`. This plugin needs to be
manually included in your plugin list; it is not part of any meta-package.

The plugin is run both on `__invoke` as well as on `__destruct`. Since the most
common usage is to perform conditional migrations (e.g. renaming a table), it is
recommended to add it at or near the beginning of your plugin list, but _at
least_ before the `dbmover/mysql-tables` plugin (included in the `dbmover/mysql`
meta-package), since this is a "destructive" plugin (it actually drops tables
not found in your schemas).

See also `dbmover/conditionals` for examples etc.

## Contributing
See `dbmover/core`.

