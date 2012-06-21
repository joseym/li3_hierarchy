<?php

namespace li3_hierarchy\extensions\inheritance;

class Block {

	protected $_block;
	protected $_name;

	public function __construct($name, $content, $template, $params){
		$this->_name = $name;
		$this->content = $content;
		$this->template = $template;
		$this->params = $params;
	}

	/**
	 * Push a parent to a block
	 * @param  [type] $content  [description]
	 * @param  [type] $template [description]
	 * @return [type]           [description]
	 */
	public function push($content, $template, $params){
		
		/**
		 * Recursively dig thru block to find highest parent
		 * adds this block there.
		 * @var function
		 */
		$_dig = function($block) use (&$content, &$template, &$params, &$_dig){

			if($block->parent()) return $_dig($block->parent);

			$block->parent = new Block($block->name(), $content, $template, $params);
			$block->parent->child = $block;

		};
		
		return $_dig($this);
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
		if(!isset($this->_block['child'])) return false;
		return $this->child;
	}

	/**
	 * The parsed version of the block
	 * @return [type] [description]
	 */
	public function parsed($content = null){
		if($content !== null) $this->parsed = $content;
		if(!isset($this->_block['parsed'])) return false;
		return $this->parsed;
	}

	public function content($content = null){
		if($content !== null) $this->content = $content;
		return $this->content;
	}

	public function name(){
		return $this->_name;
	}

	public function params(){
		return $this->params;
	}

	public function __set($name, $value){
		$this->_block[$name] = $value;
	}

	public function __get($name){
		return $this->_block[$name];
	}


}
