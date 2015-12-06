# Nature

## 什么是 Nature

一个简单的 PHP 基础库就像自然环境，规则简单而自然。是起源。

## 版本号

版本别名来自于杭州所有景点

## 引入 Nature 环境

1. 传统用法
   
   将 Nature.par 放到库目录（比如 /Library），然后在你的程序头部添加以下代码。
   
   ​	<?php
   
   ​		require('Library/Nature.phar');
   
   ​
   
2. 使用 auto_prepend_file 简化 require 步骤
   
   1) .user.ini (fpm环境) 中配置：
   
   auto_prepend_file="/var/www/Library/Nature.phar"
   
   设置 auto_prepend_file 的值为 Nature.phar 的绝对路径
   
   2) Apache mod_php 中则使用 .htaccess  
   
   php_value auto_prepend_file "/var/www/Library/Nature.phar"

## 更多介绍

暂时不吹牛了，先写代码去。

SHUT UP AND SHOW YOUR THE CODE.

## test area

此段文字用于触发WebHook