# Nature

## 什么是 Nature

一个简单的 PHP 基础库就像自然环境，规则简单而自然。是起源。

## 版本号

版本别名来自于杭州所有景点

## 引入 Nature 环境

1. 传统用法
   
   1.1 将 Nature.par 放到库目录
   
   1.2 在主目录 index.php 中
   
   ​	<?php
   
   ​		require('Library/Nature.phar');
   
   ​
   
2. 使用 auto_prepend 简化 require 步骤
   
   2.1 .user.ini (fpm环境) 中配置：
   
   auto_prepend_file="/var/www/Library/Nature.phar"
   
   设置 auto_prepend_file 的值为 Nature.phar 的绝对路径

## 更多介绍

暂时不吹牛了，先写代码去。

SHUT UP AND SHOW YOUR THE CODE.