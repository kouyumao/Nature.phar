<?php
    
    namespace Nature
    {
        //nature 工具函数
        
        /**
         * 重定向
         * !!会打断执行流程，直接退出页面
         *
         * @param string $url 要跳转的网址
         * @param int $status http 状态码
         */
        function redirect($url, $status=302)
        {
            http_response_code($status);
            header('Location: '.$url);
            exit;
        }

        /**
         * 读取或者设置配置项
         * @param $key
         * @param null $value
         * @return array|null
         */
        function configure($key,  $value=null)
        {
            $key = trim($key, "\\");
            $key = str_replace("\\", ".", $key);

            $isget = !is_null($value);
            $pointer = &App::$configure;
            $items = explode(".", $key);

            $key = array_pop($items);
            foreach($items as $item){
                if(!array_key_exists($item, $pointer)) { #set when key does not exists
                    if($isget) {
                        return null;
                    }
                    $pointer[$item] = array();
                }
                if(!is_array($pointer[$item])) {
                    return $pointer[$item];
                }
                $pointer = &$pointer[$item];
            }
            if(!is_null($value)) {
                $pointer[$key] = $value;
            }

            if(isset($pointer[$key])){
                return $pointer[$key];
            } else {
                return null;
            }
        }
        
        function singleton($className, $renewal=false)
        {
            if(is_array($className)) {
                $objects = array();
                foreach($className as $class){
                    $objects[] = singleton($class);
                }
                return $objects;
            }
            
            static $instances = array();
            static $alias = array(
                'tpl'=>'Nature\\Template',
                'db'=>'Nature\\MySQL',
                'mysql'=>'Nature\\MySQL'
            );
            if(isset($alias[$className])) {
                $className = $alias[$className];
            }
            /**
             * 初始化实例
             */
            $setup = function ($className, $cfg) {
                $object = new $className();
                if(method_exists($object, '__setup')) {
                    $cfg_key = $className;
                    $cfg = configure($cfg_key);
                    if($cfg===false) {
                        throw new \Exception($className.' Need a Configure "'.$cfg_key.'"');
                    } else {
                        call_user_func(array($object, '__setup'), $cfg);
                    }
                }
                return $object;
            };
            
            /**
             * 结束实例
             */
            $teardown = function ($instance){
                if(method_exists($instance, '__teardown')) {
                    call_user_func(array($instance, '__teardown'));
                }
                unset($instance);
            };
            
            if (!isset($instances[$className]) || $renewal) {
                global $cfg;
                if(isset($instances[$className])) {
                    $teardown($instances[$className]);
                }
                $instances[$className] = $setup($className, $cfg);
            }
            return $instances[$className];
        }
        
    }
    //register shortcut
    namespace 
    {
        function redirect($url, $status=302)
        {
            return Nature\redirect($url, $status);
        }
        
        function escape($str, $default='')
        {
            if(empty($str)){
                return $default;
            } else {
                return htmlspecialchars($str);
            }
        }
        
        function configure($name, $value=null)
        {
            return Nature\configure($name, $value);
        }
        
        function singleton($className, $renewal=false)
        {
            return Nature\singleton($className, $renewal);
        }
        
        if (!function_exists('http_response_code')) {
            function http_response_code($code = NULL) 
            {
                static $http_response_code = 200;
                if ($code !== NULL) {
                    switch ($code) {
                        case 100: $text = 'Continue'; break;
                        case 101: $text = 'Switching Protocols'; break;
                        case 200: $text = 'OK'; break;
                        case 201: $text = 'Created'; break;
                        case 202: $text = 'Accepted'; break;
                        case 203: $text = 'Non-Authoritative Information'; break;
                        case 204: $text = 'No Content'; break;
                        case 205: $text = 'Reset Content'; break;
                        case 206: $text = 'Partial Content'; break;
                        case 300: $text = 'Multiple Choices'; break;
                        case 301: $text = 'Moved Permanently'; break;
                        case 302: $text = 'Moved Temporarily'; break;
                        case 303: $text = 'See Other'; break;
                        case 304: $text = 'Not Modified'; break;
                        case 305: $text = 'Use Proxy'; break;
                        case 400: $text = 'Bad Request'; break;
                        case 401: $text = 'Unauthorized'; break;
                        case 402: $text = 'Payment Required'; break;
                        case 403: $text = 'Forbidden'; break;
                        case 404: $text = 'Not Found'; break;
                        case 405: $text = 'Method Not Allowed'; break;
                        case 406: $text = 'Not Acceptable'; break;
                        case 407: $text = 'Proxy Authentication Required'; break;
                        case 408: $text = 'Request Time-out'; break;
                        case 409: $text = 'Conflict'; break;
                        case 410: $text = 'Gone'; break;
                        case 411: $text = 'Length Required'; break;
                        case 412: $text = 'Precondition Failed'; break;
                        case 413: $text = 'Request Entity Too Large'; break;
                        case 414: $text = 'Request-URI Too Large'; break;
                        case 415: $text = 'Unsupported Media Type'; break;
                        case 500: $text = 'Internal Server Error'; break;
                        case 501: $text = 'Not Implemented'; break;
                        case 502: $text = 'Bad Gateway'; break;
                        case 503: $text = 'Service Unavailable'; break;
                        case 504: $text = 'Gateway Time-out'; break;
                        case 505: $text = 'HTTP Version not supported'; break;
                        default:
                            exit('Unknown http status code "' . htmlentities($code) . '"');
                        break;
                    }
                    $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                    header($protocol . ' ' . $code . ' ' . $text);
                    $http_response_code = $code;
                }
                return $http_response_code;
            }
        }
    }