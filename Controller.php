<?php
    namespace Nature;
    abstract class Controller 
    {
        protected $tpl;
        function __construct() 
        {
            $properties = array('db', 'tpl');
            foreach($properties as $property){
                if(property_exists($this, $property)) {
                    $this->$property = singleton($property);
                }
            }
        }
        
        function get() 
        {
            if($this->tpl->exists()){
                $this->display();
            } else {
                throw new HTTPException("Page Not Found", 404);
            }
        }
        
        
        /**
         * show_message function.
         * 
         * @access public
         * @param array $data
         * @return void
         */
        function show_message($data, $type="html")
        {
            switch($type) {
                case 'json':
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                    break;
                default:
                    $this->assign($data);
                    $this->display('message.html');
            }
        }
        
        function assign() 
        {
            call_user_func_array(array($this->tpl, 'assign'), func_get_args());
        }
        
        function display($var=null)
        {
            $this->tpl->display($var);
        }
    }