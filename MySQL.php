<?php
    namespace Nature;
    class MySQL
    {
        private $dsn;
        private $sth;
        private $dbh;
        private $user;
        private $charset;
        private $password;
        
        public $lastSQL='';
        
        function __setup($configure=array())
        {
            $this->dsn = $configure['dsn'];
            $this->user = $configure['username'];
            $this->password = $configure['password'];
            $this->charset = $configure['charset'];
            $this->connect();
        }
        
        function connect()
        {
            if(!$this->dbh){
                $this->dbh = new \PDO($this->dsn, $this->user, $this->password);
                $this->dbh->query('SET NAMES '.$this->charset);
            }
        }
        
        function beginTransaction()
        {
            if(!$this->dbh->beginTransaction()) {
                throw new MySQLException("Can not begin a database transaction.");
            }
            return $this;
        }
        
        function inTransaction()
        {
            return $this->dbh->inTransaction();
        }
        
        function prepare($sql)
        {
            $this->lastSQL = $sql;
            $this->sth = $this->dbh->prepare($sql);
            return $this;
        }
        
        function execute($sql, $parameters=[])
        {
            if(is_array($sql)) {
                $parameters = $sql;
            } else {
                $this->prepare($sql);
            }
            $this->watchException($this->sth->execute($parameters));
            return $this;
        }
        
        function rollBack()
        {
	        return $this->dbh->rollBack();
        }
        
        function commit()
        {
	        return $this->dbh->commit();
        }
        
        function watchException($execute_state)
        {
            if(!$execute_state){
                throw new MySQLException("SQL: {$this->lastSQL}\n".$this->sth->errorInfo()[2], intval($this->sth->errorCode()));
            }
        }
        
        function fetchAll($sql, $parameters=[])
        {
            $this->execute($sql, $parameters);
            $result = [];
            while($result[] = $this->sth->fetch(\PDO::FETCH_ASSOC)){ }
            array_pop($result);
            return $result;
        }
        
        function fetchColumnAll($sql, $parameters=[], $position=0)
        {
            $this->execute($sql, $parameters);
            $result = [];
            while($result[] = $this->sth->fetch(\PDO::FETCH_COLUMN, $position)){ }
            array_pop($result);
            return $result;
        }
        
        function exists($sql, $parameters=[])
        {
            $result = $this->fetch($sql, $parameters);
            return !empty($result);
        }
        
        function query($sql, $parameters=[])
        {
            $this->execute($sql, $parameters);
            return $this->sth->rowCount();
        }
        
        function fetch($sql, $parameters=[], $type=\PDO::FETCH_ASSOC)
        {
            $this->execute($sql, $parameters);
            return $this->sth->fetch($type);
        }
        
        function fetchKV($sql, $parameters=[])
        {
            $results = $this->fetch($sql, $parameters);
            if(count($results)==2) {
                $key = current($results);
                $value = next($results);
            } else {
                $key = current($results);
                $value = $results;
            }
            return [
                $key=>$value
            ];
        }
        
        /**
         * Return an array with key and values
         * the key is the first field
         * For example: SELECT 'key', 'value'
         * will return [
         *    'key'=>'value'
         * ]
         * If the fields has more than two,
         *  SELECT 'key', 'value1', 'value2'
         * it will return
         * [
         *    'key'=>[
         *          0=>'value1',
         *          1=>'value2'
         *      ]
         * ]
         */
        function fetchKVAll($sql, $parameters=[])
        {
            $results = $this->fetchAll($sql, $parameters);
            $result = current($results);
            $is_multi = (count($result) > 2);
            $arr = [];
            foreach($results as $item) {
                $key = current($item);
                if($is_multi) {
                    $arr[$key] = $item;
                } else {
                    $arr[$key] = next($item);
                }
            }
            return $arr;
        }
        
        function fetchColumn($sql, $parameters=[], $position=0)
        {
            $this->execute($sql, $parameters);
            return $this->sth->fetch(\PDO::FETCH_COLUMN, $position);
        }
        
        function update($table, $parameters=[], $condition=[], $force=false)
        {
            $table = $this->format_table_name($table);
            $sql = "UPDATE $table SET ";
            $fields = [];
            $pdo_parameters = [];
            foreach ( $parameters as $field=>$value){
                $fields[] = '`'.$field.'`=:field_'.$field;
                $pdo_parameters['field_'.$field] = $value;
            }
            $sql .= implode(',', $fields);
            $fields = [];
            $where = '';
            if(is_string($condition)) {
                $where = $condition;
            } else if(is_array($condition)) {
                foreach($condition as $field=>$value){
                    $parameters[$field] = $value;
                    $fields[] = '`'.$field.'`=:condition_'.$field;
                    $pdo_parameters['condition_'.$field] = $value;
                }
                $where = implode(' AND ', $fields);
            }
            if(empty($where) && !$force) {
                throw new MySQLException("SQL: {$this->lastSQL}\n MySQL Update Exception: Condition is empty and force=false");
            }
            if(!empty($where)) {
                $sql .= ' WHERE '.$where;
            }
            return $this->query($sql, $pdo_parameters);
        }
        
        function insert($table, $parameters=[])
        {
            $table = $this->format_table_name($table);
            $sql = "INSERT INTO $table";
            $fields = [];
            $placeholder = [];
            foreach ( $parameters as $field=>$value){
                $placeholder[] = ':'.$field;
                $fields[] = '`'.$field.'`';
            }
            $sql .= '('.implode(",", $fields).') VALUES ('.implode(",", $placeholder).')';
            
            $this->execute($sql, $parameters);
            $id = $this->dbh->lastInsertId();
            if(empty($id)) {
                return $this->sth->rowCount();
            } else {
                return $id;
            }
        }
        
        function errorInfo()
        {
	        return $this->sth->errorInfo();
        }
        
        protected function format_table_name($table)
        {
            $parts = explode(".", $table, 2);
            
            if(count($parts) > 1) {
                $table = $parts[0].".`{$parts[1]}`";
            } else {
                $table = "`$table`";
            }
            return $table;
        }
        
        function errorCode()
        {
	        return $this->sth->errorCode();
        }
    }