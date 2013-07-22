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

use \lithium\core\Libraries;
use \lithium\util\String;
use \li3_hierarchy\extensions\inheritance\BlockSet;
use \li3_hierarchy\extensions\inheritance\Block;
use \li3_hierarchy\extensions\inheritance\Lexer;
use \li3_hierarchy\extensions\inheritance\Cache;

class Parser {

	/**
	 * Regexes used to track down blocks
	 * @var array
	 */
	protected static $_terminals;

	/**
	 * array of template files
	 * @var array
	 */
	protected static $_templates;

	protected static $_template;

	protected static $_cacheFile;

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
	public static function parse($blocks, $data, $options){

		$options += array(
			'type' => 'html',
		);

		$cache = new Cache();
		
		static::$_terminals = array_flip(Lexer::terminals());

		static::$_blocks = $blocks;

		static::$_cacheFile = substr(str_ireplace('/', '_', static::$_blocks->templates(0)), 1);

		$key = static::$_cacheFile . $options['type'];

		$_pattern = "/{:(block) \"({:block})\"(?: \[(.+)\])?}(.*){\\1:}/msU";

		static::$_templates = array_reverse($blocks->templates());

		if ($cache->file(sha1($key))) {
			return sha1($key);
		} else {
			$i = 0;
			while ($i < $count = count(static::$_templates)) {
				if($i == $count) break;
				$source = static::_read(static::$_templates[$i]);
				$template = static::_replace($source, $i, $options);
				$i++;
			}
		}

		// final parent template handler
		return static::$_template;
	}

	/**
	 * Replaces blocks with content from `BlockSet`
	 * @param  blob $source template source
	 * @param  bool $final `true` if this template is this the last template
	 * @param  array $options
	 * @return blob         template source, replaced with blocks
	 */
	private static function _replace($source, $i, $options){
		$cache = new Cache();
		$cachePath = $cache->cacheDir()."/template/";

		$pattern = "/{:(block) \"({:block})\"(?: \[(.+)\])?}(.*){\\1:}/msU";

		preg_match_all(static::$_terminals['T_BLOCK'], $source, $matches);

		$_blocks = null;

		foreach($matches[2] as $index => $block){

			$_pattern = String::insert($pattern, array('block' => $block));

			$_block = static::$_blocks->blocks("{$block}");

			$_blocks = static::$_blocks;

			/**
			 * Top level content for block
			 * @var string
			 */
			$content = trim($_block->content());

			/**
			 * The request for block content in the final template
			 * @var string
			 */
			$request = $matches[4][$index];

			/**
			 * Parent/child matches/replacement
			 */
			$_parents = function($block) use (&$content, &$_parents, &$matches, &$index, &$_pattern){

				$parent = $block->parent();

				if(preg_match("/{:parent:}/msU", $content, $_matches)) { 

					if($parent){
						$content = preg_replace("/{:parent:}/msU", $parent->content(), $content);
						// go again
						return $_parents($block->parent(), $content);
					} else {
						// no parent, remove the request
						$content = preg_replace("/{:parent:}/msU", "", $content);
					}

				}

				return $content;

			};

			/**
			 * Parse the block and check to see if it's parents request it.
			 * @var method
			 */
			$_children = function($block, $content = null) use (&$_children, &$matches, &$index, &$_pattern, &$_blocks){

				$content = ($content == null) ? $block->content() : $content;

				$_block = $_blocks->blocks($block->name());
				/**
				 * If the block has a child then we're not at the bottom of the chain.
				 * We need to move up until we cant
				 * @var mixed `object` or `false`
				 */
				$child = $block->child();

				/**
				 * If the block has a parent then we cant be at the top of the chain.
				 * As long as there's a parent we need to keep moving. 
				 * @var mixed `object` or `false`
				 */
				$parent = $block->parent();

				if(preg_match("/{:child:}/msU", $content)) { 
					// Block doesn't have a child block
					if(!$child){
						// Also has no parent
						if(!$parent){
							// clear the entire block
							$content = "";
						} else {
							// Has a parent, still no child tho
							// just remove the call for child block
							$content = preg_replace("/{:child:}/msU", "", $content);
							return $_children($block, $content);
						}
					}

				// not asking for a child
				} else {

					// Has a parent
					if($parent){

						if(preg_match("/{:child:}/msU", $parent->content())){
							$content = preg_replace("/{:child:}/msU", $content, $parent->content());
							return $_children($parent, $content);
						}

					}

				}

				// must return content so we dont muck up parent
				return $content;

			};

			// parse children
			$content = $_children($_block);
			// parse parents
			$content = $_parents($_block);

			$source = preg_replace($_pattern, $content, $source);

		}

		// 0 should always be the final template
		if($i == 0){
			if($cacheable = $cache->write($source, static::$_blocks->templates(0), $_blocks, $options)){
				static::$_template = $cacheable;
			}

		}

	}

	private static function _read($template){

		if(file_exists($template)){

			$content = file_get_contents($template);

			return $content;

		}

	}

}