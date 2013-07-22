<?php

/**
 * Li3_Hierarchy Cache: Build cache if in production or if caching is turned on.
 *
 * @copyright     Copyright 2012, Josey Morton (http://github.com/joseym)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_hierarchy\extensions\inheritance;

use \lithium\core\Libraries;
use \lithium\core\Environment;
use \lithium\storage\Cache as Li3Cache;

class Cache {

	protected $_cacheDir;
	protected $_library;
	protected $_options;
	protected $_cache;

	public function __construct($options = array()){

		$this->_library = Libraries::get('li3_hierarchy');
		$this->_cacheDir = $this->_library['path'] . '/resources/tmp/cache';

		$defaults['cache'] = (Environment::get() == 'production') ? true : false;
		$this->_options = $this->_library + $defaults;
		$this->_cache = $this->_options['cache'];

	}

	public function write($source, $name, $hierarchy, $options){

		$options += array(
			'type' => null,
		);

		$key = $name . $options['type'];

		$paths['serial'] = $this->_cacheDir.'/hierarchy/'.sha1($key);
		$paths['template'] = $this->_cacheDir.'/template/'.sha1($key);

		$date = date('l jS \of F Y h:i:s A');

		$serializePath = $paths['serial'];

		$source = "<?php \n\t/** Generated on $date **/\n\r\t\$this->hierarchy = unserialize(file_get_contents(\"$serializePath\"))\n ?>" . $source;

		if(file_put_contents($paths['template'], $source) AND 
			file_put_contents($paths['serial'], serialize($hierarchy))){
			// return path to cache file.
			return $this->file(sha1($key));
		}

		return false;

	}

	/**
	 * Checks for the existance of a cache file
	 * Returns false if none exists, path to cache otherwise
	 * @param  string $name name of template
	 * @return mixed       returns false if no cache file, path otherwise
	 */
	public function file($name){

		$path = $this->_cacheDir."/template/".$name;
		$file = new \SplFileInfo($path);

		if (!$file->isFile() || !$file->isReadable())  {
			// good place to recache
			return false;
		}

		return $file->getPathname();

	}

	/**
	 * Path to cache directory
	 * @return string absolute path to cache location
	 */
	public function cacheDir(){
		return $this->_cacheDir;
	}

	public function cache(){
		return $this->_cache;
	}

}
