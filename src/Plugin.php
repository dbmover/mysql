<?php

/**
 * @package Dbmover
 * @subpackage Mysql
 */

namespace Dbmover\Mysql;

use Dbmover\Core;

/**
 * Meta-package loading all officially supported MySQL plugins.
 */
class Plugin extends Core\Plugin
{
    public function __construct(Core\Loader $loader)
    {
        parent::__construct($loader);
        $loader->loadPlugins(
            Procedures::class,
            Core\Views\Plugin::class,
            IndexesAndConstraints\Plugin::class,
            Triggers\Plugin::class,
            Tables\Plugin::class
        );
    }
}

