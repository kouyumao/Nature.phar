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
    	                $errno = $this->errno();
    	                $msg = $this->error();
    	                $error = "cURL error: ($errno) $msg";
		                throw new \Exception("Can't decode value:\"{$value}\" $error");
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

        function setopt($key, $value=null)
        {
	        if(is_array($key) && is_null($value)) {
		        curl_setopt_array($this->ch, $key);
	        } else {
		        curl_setopt($this->ch, $key, $value);
	        }
	        return $this;
        }

        function authorize($user, $password)
        {
            curl_setopt($this->ch, CURLOPT_USERPWD, $user.':'.$password);
	        return $this;
        }

        function get($url, $dataType='txt')
        {
            curl_setopt($this->ch, CURLOPT_URL, $url);
            $content = curl_exec($this->ch);
            return $this->processDataType($content, $dataType);
        }

        protected function hasFile($params=[])
        {
            foreach($params as $param) {
                if(is_object($param) && is_a($param, 'CURLFile')) {
                    return true;
                }
            }
            return false;
        }

        function post($url, $params=[], $dataType='txt')
        {
            curl_setopt($this->ch, CURLOPT_URL, $url);

            if(is_array($params) && !$this->hasFile($params)) {
                $params = http_build_query($params);
	        }
            if(!empty($params)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
            }
            $content = curl_exec($this->ch);
            return $this->processDataType($content, $dataType);
        }
    }
