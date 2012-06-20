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

		$block = $this;
		
		/**
		 * Recursively dig thru block to find highest parent
		 * adds this block there.
		 * @var function
		 */
		$_dig = function($block) use (&$content, &$template, &$_dig){

			if($block->parent()) return $_dig($block->parent);

			$block->parent = new Block($block->name(), $content, $template);
			$block->parent->child = $block;

		};
		
		return $_dig($block);
	}

	public function get($name = null){
		if($name !== null) return $this->_block[$name];
		return $this->_block;
	}

	public function parent(){
		if(!isset($this->_block['parent'])) return false;
		return $this->parent;
	}

	public function child(){
		return $this->child;
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
