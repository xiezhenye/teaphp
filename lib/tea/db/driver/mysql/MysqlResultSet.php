<?php
namespace tea::db::driver::mysql;

class MysqlResultSet {
    private $rs;

    function __construct($rs) {
        $this->rs = $rs;    
    }

    function fetch() {
        return $this->rs->fetch_assoc();
    }
    
    function count() {
        return $this->rs->num_rows();
    }

    function fetchAll() {
        $ret = array();
        while ($row = $this->fetch()) {
            $ret[]= $row;
        }
        return $ret;
    }
    
}


