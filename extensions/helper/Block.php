<?php

namespace li3_hierarchy\extensions\helper;

class Block extends \lithium\template\Helper {

	/**
	 * Smarty method that chooses the add size params for you based on the method name
	 * @param  string $method  dynamic method name
	 * @param  array $options  advertising parameters
	 * @return function        returns the `place()` method
	 */
	public function __call($method, $options){
		
		return $this->_context->hierarchy->{$method}->content();
		
	}

}