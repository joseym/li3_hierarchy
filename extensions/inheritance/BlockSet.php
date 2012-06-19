<?php

namespace li3_hierarchy\extensions\inheritance;
use li3_hierarchy\extensions\inheritance\Block;

class BlockSet {

	protected $_blocks;

	public function __construct(){
		// $this->_blocks = new self();
	}

	public function push($block, $content, $template){
		
		if(isset($this->_blocks[$block])){
			print_r('lkasdjflksa');
		}

		return $this->{$block} = new Block($block, $content, $template);

	}

	public function blocks($name = null){
		if($name !== null) {
			if(isset($this->{$name})){
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

}
