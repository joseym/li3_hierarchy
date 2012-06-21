<?php

namespace li3_hierarchy\extensions\inheritance;
use li3_hierarchy\extensions\inheritance\Block;

class BlockSet {

	protected $_blocks;

	/**
	 * Master template
	 * @var string location of master template file
	 */
	protected $_templates;

	public function __construct(){
		// $this->_blocks = new self();
	}

	public function push($block, $content, $template, $params){

		// print_r($block . ": " . $template . "\n");

		if(isset($this->_blocks[$block])){
			$this->{$block}->push($content, $template, $params);
		} else {
			$this->{$block} = new Block($block, $content, $template, $params);
		}

		return $this->_blocks;

	}

	public function blocks($name = null){
		if($name !== null) {
			if(isset($this->_blocks[$name])){
				return $this->{$name};
			}
			return false;
		} 
		return $this->_blocks;
	}

	public function __set($name, $value){
		$this->_blocks[$name] = $value;
	}

	public function __get($name){
		return $this->_blocks[$name];
	}

	/**
	 * Magic method to call a single blockset by name
	 * 
	 * @param  string 	$block  	block name
	 * @param  array 	$options 	options params
	 * @return object 				block and all it's parents/children
	 */
	public function __call($block, $options){
		return $this->blocks($block);
	}

	/**
	 * Set or Retrieve master template from hierarchy
	 * @param  [type] $template [description]
	 * @return [type]           [description]
	 */
	public function templates($template = null){
		if($template !== null) {
			if(gettype($template) == 'string') $this->_templates[] = $template;
			if(gettype($template) == 'integer') return $this->_templates[$template];
		}
		return $this->_templates;
	}

}
