<?php

namespace li3_hierarchy\extensions\inheritance;
use li3_hierarchy\extensions\inheritance\Block;

class BlockSet {

	protected $_blocks;

	/**
	 * Master template
	 * @var string location of master template file
	 */
	protected $_master;

	public function __construct(){
		// $this->_blocks = new self();
	}

	public function push($block, $content, $template){

		if(isset($this->_blocks[$block])){
			$this->{$block}->push($content, $template, $this->{$block});
		} else {
			$this->{$block} = new Block($block, $content, $template);
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
	 * Set or Retrieve master template from hierarchy
	 * @param  [type] $template [description]
	 * @return [type]           [description]
	 */
	public function master($template = null){
		if($template !== null) $this->_master = $template;
		return $this->_master;
	}

}
