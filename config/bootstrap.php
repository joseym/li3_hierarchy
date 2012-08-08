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
	'renderer' => '\li3_hierarchy\template\view\adapter\Hierarchy',
	'paths' => array(
		'template' => array(
			LITHIUM_APP_PATH . '/views/{:controller}/{:template}.{:type}.php',
			'{:library}/views/{:controller}/{:template}.{:type}.php',
		),
		'layout' => array(
			LITHIUM_APP_PATH . '/views/{:controller}/{:layout}.{:type}.php',
			'{:library}/views/{:controller}/{:layout}.{:type}.php',
		)
	)
));

Media::applyFilter('view', function($self, $params, $chain) {
	if(isset($params['handler']['renderer']) && array_pop(explode('\\', $params['handler']['renderer'])) == 'Hierarchy'){
		$params['handler']['processes'] = array(
			'all' => array('template'),
			'template' => array('template'),
			'element' => array('element')
		);
	}
	return $chain->next($self, $params, $chain);
});



?>
