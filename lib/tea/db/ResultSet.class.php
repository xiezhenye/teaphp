<?php

class ResultSet implements Iterator {
    /**
     *
     * @var PDOStatement
     */
    protected $stmt;
    protected $pos = 0;
    protected $cache = array();
    
    /**
     *
     * @param PDOStatement $stmt
     */
    function __construct($stmt) {
        $this->stmt = $stmt;
    }
    
    /**
     *
     * @return array
     */
    function fetch() {
        $ret = $this->current();
        $this->next();
        return $ret;
    }

    function fetchAll() {
        $ret = array();
        foreach ($this as $row) {
            $ret[]= $row;
        }
        return $ret;
    }
        
    function columnCount() {
        return $this->stmt->columnCount();
    }

    function count() {
        static $count = null;
        if (is_null($count)) {
            $count = $this->stmt->rowCount();
        }
        return $count;
    }

    function rowCount() {
        return $this->stmt->rowCount();
    }
    
    function next() {
        if (!$this->hasNext()) {
            return false;
        }
        $this->pos++;
        if (!isset($this->cache[$this->pos])) {
            $this->cache[$this->pos] = $this->stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    function hasNext() {
        return $this->pos < $this->count();
    }
    
    function current() {
        if (!isset($this->cache[$this->pos])) {
            $this->cache[$this->pos] = $this->stmt->fetch(PDO::FETCH_ASSOC);
        }
        $ret = $this->cache[$this->pos];
        if (empty($ret)) {
            return null;
        }
        return $this->cache[$this->pos];
    }
    
    function key() {
        return $this->pos;
    }
    
    function valid() {
        return $this->hasNext();
    }
    
    function rewind() {
        $this->pos = 0;
    }
}

