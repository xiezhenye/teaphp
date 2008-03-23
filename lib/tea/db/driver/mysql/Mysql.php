<?php
namespace tea::db::driver::mysql;
use tea::db::driver::DB;
use tea::db::driver::mysql::MysqlResultSet;

class Mysql extends DB {
    private $conn;
    
    function connect($conf) {
        $conn = mysqli_connect($conf['host'], $conf['user'], $conf['password'], $conf['name']);
        
        $this->conn = $conn;
        if (isset($conf['charset'])) {
            $this->execute("set names '".$this->quote($conf['charset'])."'");
        }
    }

    function quote($s) {
        return mysqli_real_escape_string($this->conn, $s);
    }

    function addDelimiter($s) {
        return "`$s`";
    }
    
    function query($sql) {
        $rs = $this->execute($sql);
        $ret = new MysqlResultSet($rs);
        return $ret;
    }

    function execute($sql) {
        return mysqli_query($this->conn, $sql);
    }
}
