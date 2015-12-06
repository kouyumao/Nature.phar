<?php

/**
 *
 * a Lemon build Script
 * BUILD SRC TO PHAR FILE
 *
 **/

if(ini_get('phar.readonly')==='1') {
	throw new Exception('Please set phar.readonly to Off');
}

if(php_sapi_name()==='cli'){
	$file = basename(__DIR__).'.phar';
	new build($file, __DIR__);
}

	
class build extends Phar
{
	function __construct($file, $source_dir)
	{
		if(is_file($file)) {
			unlink($file);
		}
		parent::__construct($file);
		
		$folder = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source_dir));
		$items = [];
		foreach($folder as $item) {
			if(strpos($item->getPathName(), '/.git/')) {
				continue;
			}
		    $filename = pathinfo($item->getPathName(), PATHINFO_BASENAME);
		    if(substr($filename, 0, 1) != '.') {
		        $items[substr($item->getPathName(), strlen($source_dir))] = $item->getPathName();
		    }
		}
		$this->startBuffering();
		
		$this->buildFromIterator(new ArrayIterator($items));
		$this->delete('build.php');
		$this->delete('README.md');
		
		$this->setStub("<?php
		if(is_file('phar://'.__FILE__.'/index.php')) {
			require 'phar://'.__FILE__.'/index.php';
		}
		__HALT_COMPILER();
		?>");
		$this->stopBuffering();
	}
}