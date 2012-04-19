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
		static::$_hierarchy->children = array();

	}

	public function render($template, $data = array(), array $options = array()) {

		static::$_hierarchy->children = array();

		$defaults = array('context' => array());
		$options += $defaults;

		$this->_context = $options['context'] + $this->_context;
		$this->_data = (array) $data + $this->_vars;

		$_template = $this->readTemplate($template);

		if($_template['parent']){
			static::$_hierarchy->children['children']= $_template;
			return $this->render($_template['parent'], $data, $options);
		}

		echo "<pre>";
		print_r($options['hierarchy']);
		echo "</pre>";

	}

	public function readTemplate($template){

		ob_start();
		include $template;
		$content = ob_get_clean();

		return $this->parse($content, $template);

	}

	public function parse($content, $template){

		$blocks = array();
		$_parent = (bool)false;

		foreach(static::$_patterns as $type => $pattern){

			$find = ($type == 'extends') ? preg_match( $pattern, $content, $matches ) : preg_match_all( $pattern, $content, $matches );

			if($find){

				if($type == 'extends') $_parent = static::$_hierarchy->templatePath . $matches[2];

				if(is_array($_blocks = $matches[2]) AND $type == 'blocks'){

					foreach($_blocks as $index => $block){
						$blocks[$block] = $matches[3][$index];
					}

				}

				$content = preg_replace($pattern, '', $content);

			}

		}

		return array('parent' => $_parent, 'template' => $template, 'blocks' => $blocks, 'content' => $content);

	}

	public function replace($body, $children){

		// print_r($chilsdren);
		// print_r(array($body));
		foreach ($children['blocks'] as $section => $content) {
			// print_r($content);
			// $body = preg_replace("/{:(block) \"{y$section}\"}(.*){\\1:}/msU", '', $content);
			// echo $body;
		}

		// return $body;

	}



}
