<?php
    namespace Nature;
    class HTTPException extends \Exception 
    { 
        function __construct($message="", $code=0, $previous=null)
        {
            if(is_int($message)) {
                $code = $message;
                $messsage = '';
            }
            if($code!==0) {
                http_response_code($code);
            }
            parent::__construct($message, $code, $previous);
            $tpl = singleton('tpl');
            $tpl->assign('msg', $message);
            if ($tpl->exists($code.'.html')) {
                $tpl->display($code.'.html');
            }
        }
    }