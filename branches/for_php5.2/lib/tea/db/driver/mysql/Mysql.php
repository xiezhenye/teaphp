<?php
namespace tea::db::driver::mysql;
use tea::db::driver::DB;
use tea::db::driver::mysql::MysqlResultSet;

class Mysql extends DB {
    private $conn;
    
    function addDelimiter($s) {
        return "`$s`";
    }

    public function addLimitClause($sql, $limit, $offset = 0) {
        return $sql . ' limit ' . intval($offset) . ',' . intval($limit);
    }
    

}
