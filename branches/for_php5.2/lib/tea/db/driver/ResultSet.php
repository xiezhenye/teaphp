<?php
namespace tea::db::driver;

class ResultSet {
    protected $stmt;

    function __construct($stmt) {
        $this->stmt = $stmt;    
    }

    function fetch() {
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    function fetchAll() {
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function fetchColumn($id = null) {
        return $this->stmt->fetchAll(PDO::FETCH_COLUMN, $id);
    }

    function fetchUnique($id = null) {
        return $this->stmt->fetch($id);
    }
    
    function fetchColumns() {
        $ret = array();
        $row = $this->fetch();
        foreach ($row as $k => $v) {
            $ret[$k] = array($v);
        }
        while ($row = $this->fetch()) {
            foreach ($row as $k => $v) {
                $ret[$k][] = $v;
            }
        }
        return $ret;
    }
    
    function columnCount() {
        return $this->stmt->columnCount();
    }

    function count() {
        return $this->rowCount();
    }

    function rowCount() {
        return $this->stmt->rowCount();
    }
}

