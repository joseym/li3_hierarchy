<?php

/**
 * Li3_Hierarchy Cache: Build cache if in production or if caching is turned on.
 *
 * @copyright     Copyright 2012, Josey Morton (http://github.com/joseym)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_hierarchy\extensions\inheritance;

use \lithium\core\Libraries;
use \lithium\storage\Cache as Li3Cache;

class Cache {

	protected $_cacheDir;
	protected $_library;

	public function __construct($options = array()){
		$this->_library = Libraries::get('li3_hierarchy');
		$this->_cacheDir = $this->_library['path'] . '/resources/tmp/cache';
	}

	public function write($source, $name, $hierarchy){

		$paths['serial'] = $this->_cacheDir.'/hierarchy/'.sha1($name);
		$paths['template'] = $this->_cacheDir.'/template/'.sha1($name);

		$serializePath = $paths['serial'];

		$source = "<?php \$this->hierarchy = unserialize(file_get_contents(\"$serializePath\")) ?>\n\r" . $source;

		if(file_put_contents($paths['template'], $source) AND 
			file_put_contents($paths['serial'], serialize($hierarchy))){
			// return path to cache file.
			return $this->file(sha1($name));
		}

		return false;

	}

	public function file($name){

		$path = $this->_cacheDir."/template/".$name;
		$file = new \SplFileInfo($path);

		if (!$file->isFile() || !$file->isReadable())  {
			// good place to recache
			return false;
		}

		return $file->getPathname();

	}

	public function cacheDir(){
		return $this->_cacheDir;
	}

}
