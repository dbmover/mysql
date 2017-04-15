# Dbmover/Mysql
MySQL vendor-specific metapackage for DbMover

## Installation

### Composer (recommended)
```sh
$ composer require dbmover/mysql
```

No, seriously: use Composer. As of version 0.6 DbMover uses a plugin-based
architecture where each operation is in its own package. Maintaining all these
dependencies manually is tedious and a _lot_ of work.

## Setup and running
See [the Dbmover README](http://dbmover.monomelodies.nl/docs/) for instructions
on setting up and running DbMover.

## MySQL specific notes
The MySQL plugins fully support the use of escaped object names (e.g.
`something-weird`). Note however that MySQL _is_ a bit weird with them, so our
official recommendation is to avoid reserved words or other illegal characters
in object names if at all possible.

