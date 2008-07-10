<?php

class PreparedStatement {
    protected $stmt;

    public function __construct($stmt) {
        $this->stmt = $stmt;    
    }

    public function execute($data = null) {
        $this->stmt->execute($data);
        return new ResultSet($this->stmt);    
    }

    public function bind($param, $value) {
        return $this->stmt->bindParam($param, $value);
    }
}
