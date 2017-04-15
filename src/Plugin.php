<?php

/**
 * @package Dbmover
 * @subpackage Mysql
 *
 * Meta-package loading all officially supported MySQL plugins.
 */

namespace Dbmover\Mysql;

use Dbmover\Core;
use Dbmover\Views;

class Plugin extends Core\Plugin
{
    public function __construct(Core\Loader $loader)
    {
        parent::__construct($loader);
        $loader->loadPlugins(
            Views\Plugin::class,
            Procedures::class,
            Indexes\Plugin::class,
            Constraints\Plugin::class,
            Triggers\Plugin::class,
            Tables\Plugin::class
        );
    }
}

