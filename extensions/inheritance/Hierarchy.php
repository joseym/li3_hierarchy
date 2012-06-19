<?php

namespace li3_hierarchy\extensions\inheritance;
use li3_hierarchy\extensions\inheritance\BlockSet;

class Hierarchy {

	protected $_blocks;

	public function __construct(){
		$this->_blocks = new BlockSet();
	}

	public function push($block, $content, $template){
		$this->_blocks->create($block, $content, $template);
	}

	public function get(){
		return $this->_blocks->get();
	}

}
