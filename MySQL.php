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
	        return $this->dbh->beginTransaction();
        }
        
        function inTransaction()
        {
            return $this->dbh->inTransaction();
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
            $result = [];
            $this->lastSQL = $sql;
            $this->sth = $this->dbh->prepare($sql);
            $this->watchException($this->sth->execute($parameters));
            while($result[] = $this->sth->fetch(\PDO::FETCH_ASSOC)){ }
            array_pop($result);
            return $result;
        }
        
        function fetchColumnAll($sql, $parameters=[], $position=0)
        {
            $result = [];
            $this->lastSQL = $sql;
            $this->sth = $this->dbh->prepare($sql);
            $this->watchException($this->sth->execute($parameters));
            while($result[] = $this->sth->fetch(\PDO::FETCH_COLUMN, $position)){ }
            array_pop($result);
            return $result;
        }
        
        function exists($sql, $parameters=[])
        {
            $this->lastSQL = $sql;
            $data = $this->fetch($sql, $parameters);
            return !empty($data);
        }
        
        function query($sql, $parameters=[])
        {
            $this->lastSQL = $sql;
            $this->sth = $this->dbh->prepare($sql);
            $this->watchException($this->sth->execute($parameters));
            return $this->sth->rowCount();
        }
        
        function fetch($sql, $parameters=[], $type=\PDO::FETCH_ASSOC)
        {
            $this->lastSQL = $sql;
            $this->sth = $this->dbh->prepare($sql);
             $this->watchException($this->sth->execute($parameters));
            return $this->sth->fetch($type);
        }
        
        function fetchColumn($sql, $parameters=[], $position=0)
        {
            $this->lastSQL = $sql;
            $this->sth = $this->dbh->prepare($sql);
            $this->watchException($this->sth->execute($parameters));
            return $this->sth->fetch(\PDO::FETCH_COLUMN, $position);
        }
        
        function update($table, $parameters=[], $condition=[])
        {
            $sql = "UPDATE `{$table}` SET ";
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
            if(!empty($where)) {
                $sql .= ' WHERE '.$where;
            }
            return $this->query($sql, $pdo_parameters);
        }
        
        function insert($table, $parameters=[])
        {
            $sql = "INSERT INTO `$table`";
            $fields = [];
            $placeholder = [];
            foreach ( $parameters as $field=>$value){
                $placeholder[] = ':'.$field;
                $fields[] = '`'.$field.'`';
            }
            $sql .= '('.implode(",", $fields).') VALUES ('.implode(",", $placeholder).')';
            
            $this->lastSQL = $sql;
            $this->sth = $this->dbh->prepare($sql);
            $this->watchException($this->sth->execute($parameters));
            $id = $this->dbh->lastInsertId();
            return $id;
        }
        
        function errorInfo()
        {
	        return $this->sth->errorInfo();
        }
        
        function errorCode()
        {
	        return $this->sth->errorCode();
        }
    }
    class MySQLException extends \Exception { }