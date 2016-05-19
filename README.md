# Nature

## 什么是 Nature

简单的 PHP 基础库就像自然环境，规则简单而自然。

A  simple JSON example:

```php
require 'Nature.phar';
class index extends Nature\Controller
{
  function get()
  {
  	return ["msg"=>"Hello World"];
  }
}
```

displays:

```json
{"msg":"Hello World"}
```



## 版本号

版本别名来自于杭州所有景点

## 约定

此 PHP 库约定项目要遵守 [PSR](http://www.php-fig.org/psr/) 规范。目前已经实现 PSR-1、PSR-2、PSR-4。

此外扩展 PSR-4：凡是 {$filename}.phar 文件 都应该被视为 {$filename} 文件夹。

### 构建

`php build.php` 将在目录下生成 Nature.phar 文件。

## 引入 Nature 环境

1. 传统用法

   将 Nature.par 放到库目录（比如 App），然后在你的程序头部添加以下代码。

```php
require('App/Nature.phar');
```
   ​

1. 使用 auto_prepend_file 简化 require 步骤

   1) .user.ini (fpm环境) 中配置：

   auto_prepend_file="/var/www/Library/Nature.phar"

   设置 auto_prepend_file 的值为 Nature.phar 的绝对路径

   2) Apache mod_php 中则使用 .htaccess  

   php_value auto_prepend_file "/var/www/Library/Nature.phar"


## RESTFULL

### 1. 初始化 rest 风格的 Controller

文件内放置一个 class ，并且 extends 自 `Nature\Controller`，框架即可自动初始化，并以 rest 风格调用。 `约定: 为避免混乱，一个文件内放一个 Controller `

```php
class indexController extends Nature\Controller {
    function get(){
        return 'Can you see me ?';
    }
}
```

### 2. 另一种 rest 风格的调用方式

除了支持初始化 Controller 对象，我们还支持使用 function 初始化。(仅在简单场合使用)

```php
function get() {
    return 'Can you see me ?';
}
function post() {
}
```

### 配置文件

-  App 目录下存放 configure.php，为用户自己的配置文件，具有最高的优先级。
-  Nature 目录下的 configure.php 是默认配置，可以被 App 内的 configure.php 覆盖。

举例：

```php
return [
	'Nature'=>[
		'MySQL'=>[
        'dsn'=>getenv('MYSQL_DSN'),
        'username'=>getenv('MYSQL_USER'),
        'password'=>getenv('MYSQL_PASSWORD'),
		]
	]
];
```


同时还支持用 dot(.) 来访问多级数组，举例

```php
return [
	'Nature'=>[
		'MySQL'=>[
        'dsn'=>getenv('MYSQL_DSN'),
		]
	],
	'Nature.MySQL.username'=>getenv('MYSQL_USER'),
	'Nature.MySQL.password'=>getenv('MYSQL_PASSWORD'),
];
```

上面的代码效果是一样的，而且可以任意混用。

通过 configure 函数可以读取或者设置配置：

举例：读取 mysql用户名

```php
echo configure('Nature.MySQL.username');
```

设置 mysql 用户名

```php
configure('Nature.MySQL.username', 'root');
```
### 模板
PHP 就是模板语音。框架默认选择 php 原生语法做模板。Controller 自带两个方法：assign 和 display

如果没有指定 display 的模板文件，默认使用当前文件名，并把 .php 替换成 .html

```php
class indexController extends Nature\Controller {
	function get(){
		$this->display();
		//等值于 $this->display('index.html');
	}
}
```

`App/Template/index.html`

```html
<!DOCTYPE html>
<html>
	<body>
	    <h1>Can you see me ?</h1>
	</body>	
</html>
```

小技巧：像上面的例子，你还可以写成：


```php
class indexController extends Nature\Controller { }
	// 如果指定位置的模板文件已经存在，在未编写 get 方法的时候，Controller 会自动调用 $this->display();
```

#### *Tips:*
[自 PHP5.4 起，即使 `short_open_tag = off`，`<?=` 也是可用的。](http://php.net/manual/zh/ini.core.php#ini.short-open-tag)

### 加载数据库和模板

-  约定：默认启用模板，因为 PHP 是一种模板语言。
-  需要数据库？为 Controller 设置一个 $db 属性，nature 会自动为你初始化数据库；不设置 $db 则不会初始化。

示例：`index.php`

```php
class indexController extends Nature\Controller {
	public $db;
	function get(){
		$data = $this->db->fetch("SELECT 1,2,3");
		var_dump($data);
		//$data 等值于 array(1, 2, 3)
	}
}
```



### 易于使用的单例模式

#### 约定：使用 singleton 函数用单例模式初始化一个类

-  singleton($className);
-  singleton([$className1, $className1]);

如果传入字符串，将会以字符串为类名返回一个实例；如果传入的是数组，将返回一个数组，每一项是一个示例。

这样我们可以实现这样的调用方式：

`test.php`

```php
list($db, $tpl) = singleton(['db', 'tpl']);
```

参数 $className 为类名，比如 `Nature\MySQL`。上面的 `tpl`、`db` 是一个快捷方式。

#### 配置自动传入 singleton
支持 singleton 的类举例：

在配置文件中配置：

```php
return [
	'Myspace.myclass'=>[
		//这里放 myclass 的配置文件
	]
];
```

`App/Myspace/myclass.class.php`

```php
namespace Myspace;
class myclass{
	function __setup($configure){
		//这里的 $configure 等于上面 Myspace.myclass 的内容。
	}
}
```

###  

### PSR-4 Autoload 机制

phar文件所在目录是一个 Autoload 的容器：
`new Namespace\Namespace\ClassName`
会在 App 目录下自动查找 `Namespace/Namespace/ClassName.php`

举例：
$cURL = new Nature\cURL
会自动 `include 'Nature/cURL.php';`



### 异常处理

如果要抛出一个 404 或者 500， Nature 自带了一个 HTTPException，构造函数的第一个参数是 http 状态码。

	new Nature\HTTPException($httpcode);

例如抛出 404 异常：

	new Nature\HTTPException(404);
此外，如果默认模板目录有状态码同名的 html，会被自动display。

### 更简单的cURL

Nature\cURL  对 cURL 扩展进行了封装。

HTTP Get

```php
$request = new Nature\cURL;
echo $request->get("https://www.baidu.com");
```

HTTP Post

```php
$request = new Nature\cURL;
echo $request->post("https://httpbin.org/post", ["user"=>"me"]);
```

上面的 httpbin 返回的是 json 数据，我需要直接获取 json_decode 的结果？

```php
$request = new Nature\cURL;
$data = $request->post("https://httpbin.org/post", ["user"=>"me"], "json");
var_dump($data["form"]);
```



### Credits

戴劼 daijie@php.net

