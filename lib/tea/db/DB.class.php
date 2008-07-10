<?php

abstract class DB {
    protected $db;

    public function connect($dsn, $username = null, $password = null, $driverOoptions = null) {
        $this->db = new PDO($dsn, $username, $password, $driverOoptions);
    }

    public function query($sql) {
        $stmt = $this->db->query($sql);
        return new ResultSet($stmt);
    }
    
    public function prepare($sql) {
        return new PreparedStatement($this->db->prepare($sql));
    }

    public function quote($str) {
        return $this->db->quote($str);
    }
    
    public function execute($sql) {
        return $this->db->exec($sql);
    }

    public function addDelimiter($s) {
        return $s;
    }
    
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    public function commit() {
        return $this->db->commit();
    }

    public function rollBack() {
        return $this->db->rollBack();
    }
    
    public abstract function addLimitClause($sql, $limit, $offset = 0);
}

