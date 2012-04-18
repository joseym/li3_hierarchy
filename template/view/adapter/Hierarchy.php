<?php

namespace li3_hierarchy\template\view\adapter;

use \lithium\core\Libraries;
use \lithium\core\Environment;

class Hierarchy extends \lithium\template\view\Renderer {

    protected $_smarty;

    public function __construct($config = array()) {}

	public function render($template, $data = array(), array $options = array()) {

		$defaults = array('context' => array());
		$options += $defaults;

		$template__ = (is_array($template)) ? $template[0] : $template;
		$flipped_path = array_reverse(explode("/", $template__));

		$isLayout = (preg_match('/layouts/', $flipped_path[0]) OR $flipped_path[1] == 'layouts') ? true : false;
		$isElement = (preg_match('/elements/', $flipped_path[0]) OR $flipped_path[1] == 'elements') ? true : false;
		$isView = (!$isLayout AND !$isElement) ? true : false;

		ob_start();
		include $template__;
		$content = ob_get_clean();

		return $content;

	}

}
