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
use lithium\net\http\Media;
use li3_hierarchy\extensions\inheritance\BlockSet;
use li3_hierarchy\extensions\inheritance\Block;
use li3_hierarchy\extensions\inheritance\Cache;


class Lexer {

	private static $_hierarchy;

	private static $_master;

	private static $_blockSet;

	protected static $_terminals = array(
		"/{:(block) \"([^\"]+)\"(?: \[(.+)\])?}(.*){\\1:}/msU" => "T_BLOCK",
		"/{:(parent) (\w+\s)*\"([^\"]+)\":}/msU" 	=> "T_PARENT"
	);

	/**
	 * Build the hierarchy objects
	 * @return [type] [description]
	 */
	public static function _init($options = array()){
		self::$_blockSet = new BlockSet();
		if(isset($options['hierarchy'])){
			static::$_hierarchy = $options['hierarchy'];
		}
	}

	public static function terminals(){
		return static::$_terminals;
	}

	/**
	 * Run the Lexer and gather the block objects
	 * @param  blob 	$source   	template contents
	 * @param  string 	$template 	path to template
	 * @return object|string        BlockSet object or path to cached template
	 */
	public static function run($template, $data = array(), array $options = array()){

		/**
		 * Lets check for cache, if exists then we'll return the cache file name
		 * @var Cache
		 */
		$options += array(
			'type' => 'html',
		);
		$_cache = new Cache();
		$file = static::_template($template);
		$_cacheFile = sha1($file . $options['type']);

		if($_isCached = $_cache->file($_cacheFile) AND $_cache->cache()){
			return $_isCached;
		}

		/**
		 * Guess there was no cache file, lets lex this mother.
		 */
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
					if(!empty( static::$_hierarchy)){
						list($type, $file) = array_slice($matches, 2);
						if(empty($type)){
							$type = 'template';
						} else{
							$type = trim($type);
						}

						// override the controller if more than simple file name is passed in. First directory becomes controller
						$file = explode('/', ltrim($file, '/'));
						if(count($file) > 1){
							$options['controller'] = array_shift($file);				
						}
						// set filename to template/layout
						$file = implode('/', $file);

						preg_match('/('.implode('|', Media::types()).')\.php$/', $file, $extensions);

						if(count($extensions) == 2){
							// remove .{:type}.php
							$file = substr($file, 0, strlen($file) - strlen($extensions[0]) - 1);
							// set type to render as extension specified
							$options['type'] = $extensions[1];
						}

						$options[$type] = $file;

						// template path from \lithium\template\view\adapter\File
						$_template = static::$_hierarchy->template($type, $options);
					// if $_hierarchy not passed in, use template as is
					} else {
						$_template = $matches[3];
					}
					static::run($_template, $data, $options);
				}

			}
		
		}

		return static::$_blockSet;


	}
	
	/**
	 * Modifies the template string to/from cache path
	 * @return string clean filename of template
	 */
	public static function _template($template){

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