<?php

namespace li3_hierarchy\template\view\adapter;

use \lithium\core\Libraries;
use \lithium\core\Environment;
use \lithium\core\Object;

class Hierarchy extends \lithium\template\view\adapter\File {

	protected static $_hierarchy;

	// Regex patterens used to parse template code to determine what 
	// hierarchical action to take.
	protected static $_patterns = array(
		'extends' => "/{:([a-zA-Z]+) \"([a-zA-Z 0-9 . \/]+)\":}/msU",
		'blocks' => "/{:(block) \"([a-zA-Z 0-9]+)\"}(.*){\\1:}/msU"
	);

	public function __construct(array $config = array()) {

		$defaults = array(
			'classes' => array(), 'compile' => true, 'extract' => true, 'paths' => array()
		);

		parent::__construct($config + $defaults);

		static::$_hierarchy = new \lithium\core\Object;
		static::$_hierarchy->templatePath = LITHIUM_APP_PATH . "/views/";
		static::$_hierarchy->blocks = array();

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

		$_template = $this->readTemplate($template);
		
		// The template had a {:parent:} block
		// send the parent template on thru
		if(isset($_template['parent'])){
			$parent = $_template['parent'];
			return $this->render($parent, $data, $options);

		// Template extends no further
		// replace the blocks with their content
		} else {
			return $this->replace($_template['content']);
		}

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

		return $this->parse($content, $template);

	}

	/**
	 * Parses a template file, storing the templates blocks into an object
	 * @param  string $content  contents of template
	 * @param  string $template path to template
	 * @return array           template file, blocks and full content
	 */
	private function parse($content, $template){

		$blocks = array();
		$_parent = (bool)false;

		foreach(static::$_patterns as $type => $pattern){

			$find = ($type == 'extends') ? preg_match( $pattern, $content, $matches ) : preg_match_all( $pattern, $content, $matches );

			if($find){

				if($type == 'extends') $_parent = static::$_hierarchy->templatePath . $matches[2];

				if(is_array($_blocks = $matches[2]) AND $type == 'blocks'){

					foreach($_blocks as $index => $block){
						static::$_hierarchy->blocks[$block][] = $matches[3][$index];
						$blocks[$block] = $matches[3][$index];
					}

				}

			}

		}


		$hierarchy = array('template' => $template, 'blocks' => $blocks, 'content' => $content);

		if($_parent){

			$hierarchy += array('parent' => $_parent);

		}

		return $hierarchy;

	}

	/**
	 * Replaces designated blocks with blocks set by their children
	 * @param  string $content original template contents
	 * @return string          template content with block sections replaced
	 */
	public function replace($content){

		preg_match_all( static::$_patterns['blocks'], $content, $matches );

		$sections = $matches[2];

		foreach($sections as $section){
			$pattern = "/{:block \"{$section}\"}(.*){block:}/msU";
			$content = preg_replace($pattern, static::$_hierarchy->blocks[$section][0], $content);
		}

		return $content;

	}

}
