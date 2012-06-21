<?php

/**
 * Li3_Hierarchy Lexer: Reads and tokenizes template files and returns their 
 * "blocks" as a `BlockSet`.
 *
 * @copyright     Copyright 2012, Josey Morton (http://github.com/joseym)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_hierarchy\extensions\inheritance;

use lithium\util\String;
use li3_hierarchy\extensions\inheritance\BlockSet;
use li3_hierarchy\extensions\inheritance\Block;

class Lexer {

	private static $_hierarchy;

	private static $_master;

	private static $_blockSet;

	protected static $_terminals = array(
		"/{:(block) \"([a-zA-Z 0-9]+)\"(?: \[(.+)\])?}(.*){\\1:}/msU" => "T_BLOCK",
		"/{:(parent) \"([a-zA-Z 0-9 . \/]+)\":}/msU" 	=> "T_PARENT"
	);

	/**
	 * Build the hierarchy objects
	 * @return [type] [description]
	 */
	public static function _init(){
		self::$_blockSet = new BlockSet();
	}

	public static function terminals(){
		return static::$_terminals;
	}

	/**
	 * Run the Lexer and gather the block objects
	 * @param  blob 	$source   	template contents
	 * @param  string 	$template 	path to template
	 * @return object           	BlockSet object
	 */
	public static function run($template){

		$source = self::_read($template);

		$_blockSet = static::$_blockSet;

		$template = self::_template($template);
		// Set the final template, this gets reset on every iteration to the current template
		// landing, finally, on the last.
		static::$_blockSet->templates($template);

		foreach(static::$_terminals as $pattern => $terminal ){
			
			// attempt to match block/parent regex
			$_preg = ($terminal == "T_BLOCK") ? preg_match_all($pattern, $source, $matches) : preg_match($pattern, $source, $matches);
			
			if($_preg){

				// Blocks, load them in the BlockSet
				if($terminal == "T_BLOCK"){

					foreach($matches[2] as $index => $name){

						/**
						 * Block parameters
						 * Options that can be set to modify how the block is rendered
						 * @var [type]
						 */
						$params = explode(',', $matches[3][$index]);
						$params = array_map(function($param){
							return trim($param);
						}, $params);

						static::$_blockSet->push($name, $matches[4][$index], $template, $params);
					
					}

				// Parent templates, read these and throw them back to the lexer.
				} else {

					$_template = LITHIUM_APP_PATH . "/views/{$matches[2]}";
					static::run($_template);

				}

			}
		
		}

		return static::$_blockSet;

	}
	
	/**
	 * Modifies the template string to/from cache path
	 * @return string clean filename of template
	 */
	protected static function _template($template){

		// template_{$oname}_{$stats['ino']}_{$stats['mtime']}_{$stats['size']}.php
		$_pattern = "/template_(?:[a-zA-Z]+)_(.+)\.(.+)_(?:[0-9]+)_(?:[0-9]+)_(?:[0-9]+)\.(.+)$/";

		// create the actual template file name, relative to the views directory
		if(preg_match($_pattern, $template, $matches)){
			return LITHIUM_APP_PATH . "/views/" .str_ireplace("_", "/", "{$matches[1]}.{$matches[2]}.{$matches[3]}");
		}

		return $template;
	}

	/**
	 * Reads the contents of a template file
	 * Does not parse the file.
	 * 
	 * @param string template template path to be loaded
	 * @return blob content of template file
	 */
	private static function _read($template){

		if(file_exists($template)){

			$content = file_get_contents($template);

			return $content;

		}

		return "{$template} doesn't exist";

	}

}