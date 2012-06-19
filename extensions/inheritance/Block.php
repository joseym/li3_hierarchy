<?php

namespace li3_hierarchy\extensions\inheritance;

class Block {

	protected $_block;
	protected $_name;

	public function __construct($name, $content, $template){
		$this->_name = $name;
		$this->content = $content;
		$this->template = $template;
	}

	/**
	 * Push a parent to a block
	 * @param  [type] $content  [description]
	 * @param  [type] $template [description]
	 * @return [type]           [description]
	 */
	public function push($content, $template){
		$this->parent = new Block($this->_name, $content, $template);
		// return $this;
	}

	public function get($name = null){
		if($name !== null) return $this->_block[$name];
		return $this->_block;
	}

	public function parent(){
		return $this->parent;
	}

	public function content(){
		return $this->content;
	}

	public function name(){
		return $this->_name;
	}

	public function __set($name, $value){
		$this->_block[$name] = $value;
	}

	public function __get($name){
		return $this->_block[$name];
	}


}
