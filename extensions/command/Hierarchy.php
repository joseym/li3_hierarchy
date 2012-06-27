<?php

namespace li3_hierarchy\extensions\command;
use \lithium\core\Libraries;

class Hierarchy extends \lithium\console\Command {

	private $_library;
	private $_cacheDir;

	public function __construct($config){
		parent::__construct($config);
		$this->_library = Libraries::get('li3_hierarchy');
		return $this->_cacheDir = $this->_library['path'] . '/resources/tmp/cache';
	}

    public function run() {

        $this->header('This will delete the cache from your li3_hierarchy plugin!', 80);

        if($this->in('Continue? (y/n)', array('choices' => array('y', 'n'), 'default' => 'y')) != 'y'){
        	return $this->out("Cancelling...");
        }

        $this->hr();
        $this->out('Deleting Template Cache!');
       	$this->_delete($this->_cacheDir . "/template");
        $this->hr();
        $this->out('Deleting Hierarchical Blocks!');
       	$this->_delete($this->_cacheDir . "/hierarchy");

    }
    
    /**
     * loops thru files and removes them
     * @param  string $path path to cache location
     */
    private function _delete($path){

		$files = glob("{$path}/*"); // get all file names
		foreach($files as $file){ // iterate files
			if(is_file($file)) unlink($file); // delete file
		}

    }

}

?>