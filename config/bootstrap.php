<?php

use lithium\core\Libraries;
use lithium\core\ConfigException;
use lithium\template\View;
use lithium\net\http\Media;

// Define path to plugin and other constants
defined('LI3_HIERARCHY_PATH') OR define('LI3_HIERARCHY_PATH', dirname(__DIR__));

/**
 * Map to the new renderer
 */
Media::type('default', null, array(
    'view' => '\lithium\template\View',
    'renderer' => '\li3_smarty\template\view\adapter\Hierarchy',
    'paths' => array(
        'layout' => array(
            LITHIUM_APP_PATH . '/views/{:controller}/{:template}.{:type}.php',
            '{:library}/views/{:controller}/{:template}.{:type}.php',
        )
    )
));

?>
