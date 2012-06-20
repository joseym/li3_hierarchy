<?php

/**
 * Li3_Hierarchy Parser: Parses final template and replaces "blocks" with `block`
 * from the top level block in the `BlockSet`.
 *
 * @copyright     Copyright 2012, Josey Morton (http://github.com/joseym)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Todo List:
 * 
 * 1. final block content section should be an array in the order of 
 *    rendered content
 */

namespace li3_hierarchy\extensions\inheritance;

use lithium\util\String;
use li3_hierarchy\extensions\inheritance\BlockSet;
use li3_hierarchy\extensions\inheritance\Block;
use li3_hierarchy\extensions\inheritance\Lexer;

class Parser {

	/**
	 * Regexes used to track down blocks
	 * @var array
	 */
	protected static $_terminals;

	/**
	 * Template source code
	 * @var blob
	 */
	protected static $_source;

	/**
	 * `BlockSet` object, set from render method
	 * @var object
	 */
	protected static $_blocks;

	/**
	 * Parse the template
	 * @param  blob 	$source template source code
	 * @param  object 	$blocks `BlockSet` object
	 * @return [type]         [description]
	 */
	public static function run($source, $blocks){

		static::$_terminals = array_flip(Lexer::terminals());

		static::$_source = $source;

		static::$_blocks = $blocks;

		$_pattern = "/{:(block) \"({:block})\"(?: \[(.+)\])?}(.*){\\1:}/msU";
		
		return static::_replace(static::$_source, $_pattern);

	}

	/**
	 * Replaces blocks with content from `BlockSet`
	 * @param  blob $source template source
	 * @return blob         template source, replaced with blocks
	 */
	private static function _replace($source = null, $pattern){

		if($source == null) $source = static::$_source;

		preg_match_all(static::$_terminals['T_BLOCK'], $source, $matches);

		foreach($matches[2] as $index => $block){

			$_pattern = String::insert($pattern, array('block' => $block));

			$_block = static::$_blocks->blocks("{$block}");

			$content = $_block->content();

			/**
			 * Child matches/replacement
			 */
			if(preg_match("/{:child:}/msU", $matches[4][$index])){

				if(!$_block->parent()) {
					$content = preg_replace($_pattern, "", $matches[0][$index]);
				} else {
					$content = preg_replace("/{:child:}/msU", $content, $matches[4][$index]);
				}

			}

			/**
			 * Parent matches/replacement
			 */
			if(preg_match("/{:parent:}/msU", $content)) { 
				$content = preg_replace("/{:parent:}/msU", $_block->parent()->content(), $content);
			}

			$source = preg_replace($_pattern, $content, $source);

		}

		return $source;

	}

}