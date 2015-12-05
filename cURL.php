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
                    return json_decode($value, true);
                default:
                    return $value;
            }
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
            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($params));
            $content = curl_exec($this->ch);
            return $this->processDataType($content, $dataType);
        }
    }