<?php
namespace tea::db::driver;

abstract class DB {
    protected $db;

    public function connect($dsn, $username = null, $password = null, $driverOoptions = null) {
        $this->db = new PDO($dsn, $username, $password, $driverOoptions);
    }

    public function query($sql) {
        return new ResultSet($this->db->query($sql));
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

    public function insert($table, $row) {
        $sql = 'insert into ' . $this->addDelimiter($table);
        $columns = array();
        $values = array();
        foreach ($row as $k => $v) {
            $columns[]= $this->addDelimiter($k);
            $values[]= $this->quote($v);
        }
        $sql = 'insert into ' . $this->addDelimiter($table) . 
            '(' . implode(',', $columms) . ') values(' .
            implode(',', $values) . ')';
        return $this->execute($sql);
    }
    
    public function update($table, $data, $cond) {
        $sql = 'update ' . $this->addDelimiter($table);
    }

    public abstract function addLimitClause($sql, $limit, $offset);
}

