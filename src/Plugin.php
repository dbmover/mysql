<?php

/**
 * @package Dbmover
 * @subpackage Mysql
 *
 * Meta-package loading all officially supported MySQL plugins.
 */

namespace Dbmover\Mysql;

use Dbmover\Core;
use Dbmover\HoistDrop;
use Dbmover\Views;
use Dbmover\Constraints;

class Plugin extends Core\Plugin
{
    public function __construct(Core\Loader $loader)
    {
        parent::__construct($loader);
        $loader->loadPlugins(
            Conditionals\Plugin::class,
            Views\Plugin::class,
            Procedures::class,
            Constraints\Plugin::class,
            Indexes\Plugin::class
        );
    }
}

