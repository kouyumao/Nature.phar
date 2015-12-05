#!/usr/bin/env php
<?php

	/**
	 * a Lemon build Script
	 * BUILD SRC TO PHAR FILE
 	 **/

	if(ini_get('phar.readonly')==='1') {
		throw new Exception('Please set phar.readonly to Off');
	}

	$dir = __DIR__;             // 需要打包的目录
	$file = basename($dir).'.phar';      // 包的名称, 注意它不仅仅是一个文件名, 在stub中也会作为入口前缀
	$phar = new Phar($file);
	// 开始打包
	$phar->startBuffering();
	$phar->buildFromDirectory($dir);
	$phar->delete('make.php');
	// 设置入口
	$phar->setStub("<?php
	Phar::mapPhar('{$file}');
	require 'phar://{$file}/index.php';
	__HALT_COMPILER();
	?>");
	$phar->stopBuffering();
	// 打包完成
