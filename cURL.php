<?php
    namespace Nature;
    class cURL 
    {
        private $ch;
    
        function __construct()
        {
            $this->ch = curl_init();;
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        }
        
        function __setup($cfg)
        {
            curl_setopt($this->ch, CURLOPT_TIMEOUT, isset($cfg['timeout']) ? $cfg['timeout'] : 10);
        }
        
        function processDataType($value, $type='txt')
        {
            switch($type){
                case 'json':
	                $decode = json_decode($value, true);
	                if(is_null($decode)) {
		                throw new Exception("Can't decode value:\"{$value}\"");
		                return $value;
	                } else {
                    	return $decode;
	                }
	                break;
                default:
                    return $value;
            }
        }
        
        function errno()
        {
	        return curl_errno($this->ch);
        }
        
        function error()
        {
	        return curl_error($this->ch);
        }
        
        function getinfo($type)
        {
	        $key = 'CURLINFO_'.strtoupper($type);
	        $constant = constant($key);
	        return curl_getinfo($this->ch, $constant);
        }
        
        function authorize($user, $password)
        {
            curl_setopt($this->ch, CURLOPT_USERPWD, $user.':'.$password);
        }
        
        function get($url, $dataType='txt')
        {
            curl_setopt($this->ch, CURLOPT_URL, $url);
            $content = curl_exec($this->ch);
            return $this->processDataType($content, $dataType);
        }
        
        function post($url, $params=[], $dataType='txt')
        {
	        if(is_array($params)) {
		        $params = http_build_query($params);
	        }
            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
            $content = curl_exec($this->ch);
            return $this->processDataType($content, $dataType);
        }
    }