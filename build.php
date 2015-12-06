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
	$file = basename($dir).'.phar';
	new build($file);
}

	
class build extends Phar
{
	function __construct($file)
	{
		parent::__construct($file);
		$dir = __DIR__;
		
		$this->startBuffering();
		$this->buildFromDirectory($dir);
		$this->delete('build.php');
		
		$this->setStub("<?php
		Phar::mapPhar('{$file}');
		require 'phar://{$file}/index.php';
		__HALT_COMPILER();
		?>");
		$this->stopBuffering();
	}
}