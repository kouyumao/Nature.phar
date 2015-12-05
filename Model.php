<?php
    namespace Nature;
    class Model 
    {
        protected $db;
        
        function __construct()
        {
            $this->db = singleton('db');
        }
    }