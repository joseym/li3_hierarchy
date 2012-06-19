<?php
namespace li3_hierarchy\extensions\inheritance;

use lithium\util\String;
// use li3_hierarchy\extensions\inheritance\Hierarchy;
use li3_hierarchy\extensions\inheritance\BlockSet;
use li3_hierarchy\extensions\inheritance\Block;

class Lexer {

	private static $_hierarchy;

	private static $_blockSet;

	protected static $_terminals = array(
		"/{:(block) \"([a-zA-Z 0-9]+)\"}(.*){\\1:}/msU" => "T_BLOCK",
		"/{:(parent) \"([a-zA-Z 0-9 . \/]+)\":}/msU" 	=> "T_PARENT"
	);

	/**
	 * Build the hierarchy objects
	 * @return [type] [description]
	 */
	public static function _init(){
		self::$_blockSet = new BlockSet();
	}

	/**
	 * Run the Lexer and gather the block objects
	 * @param  blob 	$source   	template contents
	 * @param  string 	$template 	path to template
	 * @return object           	BlockSet object
	 */
	public static function run($template){

		$source = self::_read($template);

		foreach(static::$_terminals as $pattern => $terminal ){
			
			$_preg = ($terminal == "T_BLOCK") ? preg_match_all($pattern, $source, $matches) : preg_match($pattern, $source, $matches);

			if($_preg){

				if($terminal == "T_BLOCK"){

					foreach($matches[2] as $index => $name){
						self::$_blockSet->push($name, $matches[3][$index], self::_template($template));
						continue;
					}

				} else {

					print_r('lkajslfakjfll');
					$template = LITHIUM_APP_PATH . "/views/{$matches[2]}";
					static::run($template);

				}

			}
		
			return static::$_blockSet;

		}

	}
	
	/**
	 * Modifies the template string to/from cache path
	 * @return [type] [description]
	 */
	protected static function _template($template){

		// template_{$oname}_{$stats['ino']}_{$stats['mtime']}_{$stats['size']}.php
		$_pattern = "/template_(?:[a-zA-Z]+)_(.+)\.(.+)_(?:[0-9]+)_(?:[0-9]+)_(?:[0-9]+)\.(.+)$/";

		// create the actual template file name, relative to the views directory
		if(preg_match($_pattern, $template, $matches)){
			return str_ireplace("_", "/", "{$matches[1]}.{$matches[2]}.{$matches[3]}");
		}

		return $template;
	}

	private static function _read($template){

		ob_start();
		include $template;
		$content = ob_get_clean();

		print_r($content);
		return $content;

	}

}