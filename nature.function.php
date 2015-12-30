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
    }