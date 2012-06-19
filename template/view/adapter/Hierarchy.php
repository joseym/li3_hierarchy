<?php

namespace li3_hierarchy\template\view\adapter;

use \lithium\core\Libraries;
use \lithium\core\Environment;
use \lithium\core\Object;
use \li3_hierarchy\extensions\inheritance\Lexer;

class Hierarchy extends \lithium\template\view\adapter\File {

	protected static $_hierarchy;

	protected static $_blocks;

	public function __construct(array $config = array()) {

		$defaults = array(
			'classes' => array(), 'compile' => true, 'extract' => true, 'paths' => array()
		);

		parent::__construct($config + $defaults);

		// Start the hierarchy lexer
		static::$_hierarchy = Lexer::_init();

	}

	/**
	 * Render the template
	 * Starts with the view and rolls up the chain of inheritance
	 * @param  string $template full path to template file
	 * @param  array  $data     data vars
	 * @param  array  $options
	 * @return string           parsed template with block sections replaced
	 */
	public function render($template, $data = array(), array $options = array()) {

		$defaults = array('context' => array());
		$options += $defaults;

		$this->_context = $options['context'] + $this->_context;
		$this->_data = (array) $data + $this->_vars;
		$template__ = $template;

		unset($options, $template, $defaults, $data);

		if ($this->_config['extract']) {
			extract($this->_data, EXTR_OVERWRITE);
		} elseif ($this->_view) {
			extract((array) $this->_view->outputFilters, EXTR_OVERWRITE);
		}

		$source = $this->readTemplate($template__);

		static::$_blocks = Lexer::run($template__);

		print_r(static::$_blocks);

		return $source;

	}

	/**
	 * Reads the template file and sets its contents to a variable
	 * @param  string $template path to template file
	 * @return array           returns the templates blocks, whether is is a child and its content
	 */
	private function readTemplate($template){

		ob_start();
		include $template;
		$content = ob_get_clean();

		return $content;

	}

	/**
	 * Parses a template file, storing the templates blocks into an object
	 * @param  string $content  contents of template
	 * @param  string $template path to template
	 * @return array           template file, blocks and full content
	 */
	// private function parse($content, $template){

	// 	$blocks = array();
	// 	$_parent = (bool)false;

	// 	foreach(static::$_patterns as $type => $pattern){

	// 		$find = ($type == 'extends') ? preg_match( $pattern, $content, $matches ) : preg_match_all( $pattern, $content, $matches );

	// 		if($find){

	// 			if($type == 'extends') $_parent = static::$_hierarchy->templatePath . $matches[2];

	// 			if(is_array($_blocks = $matches[2]) AND $type == 'blocks'){

	// 				foreach($_blocks as $index => $block){
	// 					static::$_hierarchy->blocks[$block][] = $matches[3][$index];
	// 					$blocks[$block] = $matches[3][$index];
	// 				}

	// 			}

	// 		}

	// 	}


	// 	$hierarchy = array('template' => $template, 'blocks' => $blocks, 'content' => $content);

	// 	if($_parent){

	// 		$hierarchy += array('parent' => $_parent);

	// 	}

	// 	return $hierarchy;

	// }

	/**
	 * Replaces designated blocks with blocks set by their children
	 * @param  string $content original template contents
	 * @return string          template content with block sections replaced
	 */
	// public function replace($content){

	// 	preg_match_all( static::$_patterns['blocks'], $content, $matches );

	// 	$sections = $matches[2];

	// 	foreach($sections as $section){
	// 		$pattern = "/{:block \"{$section}\"}(.*){block:}/msU";
	// 		$content = preg_replace($pattern, static::$_hierarchy->blocks[$section][0], $content);
	// 	}

	// 	return $content;

	// }

}
