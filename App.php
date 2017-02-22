<?php
    namespace Nature;
    
    define('VERSION', '1.0.0');
    define('VERSION_NAME', 'Longjing');
    define('ROOT', __DIR__);
    
    /**
     * nature library 核心类
     */
    require_once __DIR__.'/nature.function.php';
    
    class App 
    {
        static $configure = array();
        public $errors = [];
        protected $auto_run;
        
        function __construct($app_dir=null) 
        {
            if (is_null($app_dir)) {
                if(class_exists('Phar') && \Phar::running(false)) {
                    $app_dir = dirname(\Phar::running(false));
                } else {
                    $app_dir = realpath(ROOT.'/../');
                }
            }
            define('APP_DIR', $app_dir);
            set_include_path(get_include_path().':'.ROOT);
            $this->load_config();
            set_exception_handler(array($this, 'exception_handler'));
            set_error_handler(array($this, 'error_handler'));
            $this->set_psr4_autoload('Nature', __DIR__);
            define('DEBUG', configure('debug'));
            $this->power();
        }
        
        function __destruct()
        {
            if($this->auto_run) {
                $this->run();
            }
        }
        
        /**
         * 异常处理程序
         */
        function exception_handler($exception)
        {
            if(!is_a($exception, 'Nature\HTTPException')) {
                http_response_code(500);
                $tpl = singleton('tpl');
                $tpl->assign('exception', $exception);
                $tpl->assign(array(
                    'errno'=>$exception->getCode(),
                    'errstr'=>$exception->getMessage(),
                    'errfile'=>$exception->getFile(),
                    'errline'=>$exception->getLine()
                ));
                $tpl->assign('errors', $this->errors);
                $tpl->display('500.html');
            }
        }
        
        function error_handler($errno, $errstr, $errfile, $errline)
        {
            $this->errors[] = [$errno, $errstr, $errfile, $errline];
            return true;
        }
        
        /**
         * 解析配置程序
         */
        function parse_config($configure, &$position)
        {
            if(!is_array($configure)) {
                return null;
            }
            foreach($configure as $key=>$value){
                $pointer = &$position;
                $keys = explode('.', $key);
                $key = array_pop($keys);
                foreach($keys as $item){
                    $pointer = &$pointer[$item];
                }
                if(is_array($value)) {
                    self::parse_config($value, $pointer[$key]);
                } else {
                    $pointer[$key] = $value;
                }
            }
        }
        
        /**
         * 加载配置文件
         */
        function load_config($file=null)
        {
            if(is_null($file)) {
                $this->load_config([
                    __DIR__.'/configure.php',
                    APP_DIR.'/configure.php'
                ]);
            } else if(is_string($file)) {
                if(file_exists($file)) {
                    self::parse_config(include($file), self::$configure);
                }
            } else if(is_array($file)) {
                $files = $file;
                foreach($files as $file) {
                    self::load_config($file);
                }
            }
            return self::$configure;
        }
        
        function get_args($func, $from)
        {
            $args = array();
            foreach($func->getParameters() as $param){
                $name = $param->getName();
                /**
                 * casting "form-field" to "$form_field"
                 */
                if(strpos($name, '_')!==false) {
                    $replace_key = str_replace('_', '-', $name);
                    if(!isset($from[$name]) && isset($from[$replace_key])) {
                        $from[$name] = $from[$replace_key];
                        unset($from[$replace_key]);
                    }
                }
                $index = $param->getPosition();
                $args[$index] = null;
                if($param->isDefaultValueAvailable()){
                    $args[$index] = $param->getDefaultValue();
                }
                if(isset($from[$name])) {
                    $type = gettype($args[$index]);
                    $typecasting = [
                        'boolean'=>'boolval',
                        'integer'=>'intval',
                        'double'=>'floatval',
                        'string'=>'strval'
                    ];
                    if(isset($typecasting[$type])) {
                        $args[$index] = call_user_func($typecasting[$type], $from[$name]);
                    } else if($type==='array') {
                        if(is_array($from[$name])) {
                            $args[$index] = $from[$name];
                        }
                    } else if(is_null($args[$index])) {
                        $args[$index] = $from[$name];
                    }
                }
            }
            return $args;
        }
        
        function rest($object=null)
        {
            $method = strtolower($_SERVER['REQUEST_METHOD']);
            $types = array(
                'post'=>$_POST,
                'get'=>$_GET,
                'head'=>$_GET,
                'delete'=>$_REQUEST,
                'put'=>$_REQUEST
            );
            $params = $types[$method];
            try {
                if(!is_null($object)) {
                    $func = new \ReflectionMethod($object, $method);
                } else {
                    $func = new \ReflectionFunction($method);
                }
                $args = $this->get_args($func, $params);
                if(!is_null($object)) {
                    $returnData = $func->invokeArgs($object, $args);
                } else {
                    $returnData = $func->invokeArgs($args);
                }
                $content_type = false;
                switch (gettype($returnData)) {
                    case 'array':
                        $this->content_type('application/json');
                        echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
                        break;
                    case 'string':
                    case 'integer':
                    case 'float':
                    case 'double':
                        echo $returnData;
                        break;
                    case 'object':
                        if(method_exists($returnData, 'saveXML')) {
                            $this->content_type('text/xml');
                            echo $returnData->saveXML();
                        } else if(method_exists($returnData, '__toString')) {
                            echo strval($returnData);
                        }
                        break;
                }
                return true;
            } catch (\ReflectionException $e) {
                return false;
            }
        }
        
        /**
         * alias of rest
         */
        function call_function()
        {
            $this->rest();
        }
        
        function call_controller() 
        {
            foreach (get_declared_classes() as $class) {
                $reflection = new \ReflectionClass($class);
                if($reflection->isSubclassOf('Nature\\Controller') && !$reflection->isAbstract()) {
                    $obj = $reflection->newInstance();
                    $this->rest($obj);
                }
            }
        }
        
        function run()
        {
            $this->call_controller();
            $this->call_function();
            $this->hook('PageUnload');
        }
        
        function hook($event)
        {
            $className = '\\NatureHook\\'.$event;
            if(class_exists($className)) {
                $hooker = new $className();
            }
        }
        
        function set_psr4_autoload($namespace, $mapping_dir=null)
        {
            $extend_psr4 = false;
            if(is_null($mapping_dir)) {
                $mapping_dir = $namespace;
                $namespace = '';
                $extend_psr4 = true;
            }
            
            if(substr($mapping_dir, -1)!=='/') {
                $mapping_dir .= '/';
            }
            spl_autoload_register(function($class) use ($namespace, $mapping_dir){

                $len = strlen($namespace);
                if (strncmp($namespace, $class, $len) !== 0) {
                    return;
                }

                $relative_class = substr($class, $len);
                
                $file =  str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';
                if (file_exists($mapping_dir.$file)) {
                    require $mapping_dir.$file;
                } else {
                    $mapping_dir = $mapping_dir.strstr($file, DIRECTORY_SEPARATOR, true).'.phar';
                    $file = substr(strstr($file, DIRECTORY_SEPARATOR), 1);
                    if (file_exists('phar://'.$mapping_dir.DIRECTORY_SEPARATOR.$file)) {
                        require 'phar://'.$mapping_dir.DIRECTORY_SEPARATOR.$file;
                    } 
                }
            });
        }
        
        function content_type($content_type)
        {
            if(!headers_sent() && $content_type) {
                header('Content-Type: '.$content_type);
            }
        }
        
        /**
         * power by information
         */
        function power()
        {
            if (configure('x-powered-by')) {
               header('X-Powered-By: Nature/'.VERSION.' ('.VERSION_NAME.')'); 
            }
        }
        
        function set_auto_run($mode=false)
        {
            $this->auto_run = $mode;
        }
    }
