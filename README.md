# Dbmover/Mysql
MySQL vendor-specific metapackage for DbMover

## Installation

### Composer (recommended)
```sh
$ composer require dbmover/mysql
```

## Setup and running
See [the Dbmover README](http://dbmover.monomelodies.nl/core/docs/) for
instructions on setting up and running DbMover.

For quick start, you can simply add the `Dbmover\Mysql\Plugin` meta package
to your `dbmover.json` config.

## MySQL specific notes
The MySQL plugins fully support the use of escaped object names (e.g.
`something-weird`). Note however that MySQL _is_ a bit weird with them, so our
official recommendation is to avoid reserved words or other illegal characters
in object names if at all possible.

