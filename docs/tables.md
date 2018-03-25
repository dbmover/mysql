# Tables
MySQL-specific plugin for table migrations.

## Caveats/notes
Inline foreign key definitions aren't supported (yet). Use separate `ALTER
TABLE` statements instead.

Inline index definition support is also sketchy. For now, prefer to explicitly
define your indexes (`CREATE INDEX ...`). If you can't be bothered to type names
for all your indexes, check out the `Dbmover\Core\ForceNamedIndexes` plugin.

Primary keys _are_ allowed inside table definitions, on single as well as
multiple columns.

## Todo
Support storage engine and collation mutations.

