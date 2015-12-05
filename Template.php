<?php
    namespace Nature;
    
    class Template 
    {
        private $root;
        private $values=array();
        
        function __setup($configure)
        {
            $this->root = $configure['root'];
            set_include_path(get_include_path().PATH_SEPARATOR.$this->root.PATH_SEPARATOR.ROOT.'/template');
            $this->load_plugin();
        }
        
        function assign($key, $value=null)
        {
            if(is_array($key)) {
                $this->values = array_merge($this->values, $key);
            } else {
                $this->values[$key] = $value;
            }
        }
        
        function load_plugin()
        {
            $file = stream_resolve_include_path('plugin/template.functions.php');
            if ($file)
            {
                require($file);
            }
        }
        
        function get_template_filename($file=null)
        {
            $dir = dirname($_SERVER['SCRIPT_NAME']);
            if(is_null($file)){
                $file = basename($_SERVER['SCRIPT_NAME'], '.php').'.html';
            }
            if($dir==='/'){
                $dir = '';
            }
            return trim($dir.'/'.$file, '/');
        }
        
        function exists($file=null)
        {
            $realpath = $this->realpath($file);
            return $realpath!==false;
        }
        
        function realpath($file=null)
        {
            if(is_null($file)) {
                $file = $this->get_template_filename($file);
            }
            if (DEBUG) {
                clearstatcache();
            }
            return stream_resolve_include_path($file);
        }
        
        function display($file=null)
        {
            $realpath = $this->realpath($file);
            if(!$realpath) {
                $file = $this->get_template_filename($file);
                throw new \Exception("Template:<code>{$file}</code> Not Found");
            }
            extract($this->values);
            //include_once(__DIR__.'/template.function.php');
            require($realpath);
        }
    }