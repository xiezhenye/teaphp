<?php

/**
 * Mysql 结果集
 *
 */
class MysqlResultSet implements  Countable,Iterator{
    
    protected $rs;
    protected $pos = 0;
    protected $cache = array();
    protected $count = null;
    
    /**
     * @param resource $rs mysql原生结果资源
     */
    function __construct($rs) {
        $this->rs = $rs;
    }
    
    /**
     * 取得一个结果数组并下移指针
     * 
     * @return array
     */
    function fetch() {
        $ret = $this->current();
        $this->next();
        return $ret;
    }
    
    /**
     * 取得全部结果数组
     *
     * @return array
     */
    function fetchAll() {
        $ret = array();
        foreach ($this as $row) {
            $ret[]= $row;
        }
        return $ret;
    }
    
    /**
     * 结果的列数
     * 
     * @return int
     */
    function columnCount() {
        return mysql_num_fields($this->rs);
        
    }
    
    /**
     * 结果的行数
     * @return int
     */
    function count() {
        if (is_null($this->count)) {
            $this->count = mysql_num_rows($this->rs);
        }
        return $this->count;
    }
    
    /**
     * 下移指针
     * @return bool
     */
    function next() {
        if (!$this->hasNext()) {
            return false;
        }
        $this->pos++;
        if (!isset($this->cache[$this->pos])) {
            $this->cache[$this->pos] = mysql_fetch_assoc($this->rs);
        }
        return true;
    }
    
    /**
     * 指针是否已经到末尾
     *
     * @return bool
     */
    function hasNext() {
        return $this->pos < $this->count();
    }
    
    /**
     * 返回当前的行
     * @return array
     */
    function current() {
        if ($this->pos == 0 && !isset($this->cache[$this->pos])) {
            $this->cache[$this->pos] = mysql_fetch_assoc($this->rs);
        }
        $ret = $this->cache[$this->pos];
        if (empty($ret)) {
            return null;
        }
        return $this->cache[$this->pos];
    }
    
    /**
     * 返回当前的指针
     * @return int
     */
    function key() {
        return $this->pos;
    }
    
    /**
     * 指针是否已经到末尾，hasNext的别名
     * @return bool
     */
    function valid() {
        return $this->hasNext();
    }
    
    /**
     * 重置指针为0
     *
     */
    function rewind() {
        $this->pos = 0;
    }
    
}



