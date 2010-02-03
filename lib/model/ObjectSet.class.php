<?php
/**
 * 对象集合
 *
 */
class ObjectSet implements  Countable,Iterator {
    
    protected $rs = array();
    protected $className;
    protected $pos = 0;
    
    /**
     *
     * @var array
     */
    protected $conf;
    
    /**
     *
     * @param ResultSet $rs
     * @param string $class
     * @param array $conf
     */
    function __construct($rs, $class, $conf) {
        $this->className = $class;
        $this->conf = $conf;
        foreach ($rs->fetchAll() as $row) {
            $this->rs[]= $this->row2Obj($row);
        }
    }
    
    protected function row2Obj($row) {
        if (class_exists($this->className)) {
            $class = $this->className;
            return new $class($row, $this->conf['id'], false);
        } else {
            return new Record($row, $this->conf['id'], false);
        }
    }
    
    /**
     * 得到当前对象
     * 
     * @return Record
     */
    function fetch() {
        return $this->current();
    }
    
    
    /**
     * 得到携带的记录集
     * 
     * @return ResultSet
     */
    function getResultSet() {
        return $this->rs;
    }
    
    ///**
    // * 结果的属性数
    // * @return int
    // */
    //function propertyCount() {
    //    return $this->rs->columnCount();
    //}
    
    /**
     * 结果的个数
     * @return int
     */
    function count() {
        return count($this->rs);
    }
    
    /**
     * 移动指针到下一个结果
     * 
     */
    function next() {
        $this->pos++;
    }
    
    /**
     * 是否已经移动到结尾
     * 
     */
    function hasNext() {
        return $this->valid();
    }
    
    /**
     * 得到当前对象
     *
     * @return Record
     */
    function current() {
        return isset($this->rs[$this->pos]) ? $this->rs[$this->pos] : null;
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
        return $this->pos < count($this->rs);
    }
    
    /**
     * 重置指针为0
     *
     */
    function rewind() {
        return $this->pos = 0;
    }
    
    /**
     * 附加从属对象
     * 
     *
     * @param string $property 属性
     * @param Repository $repo 目标对象对应的 Repository 实例
     */
    function attach($property, $repo) {
        $fk = $this->conf['hasOne'][$property]['field'];
        $fks = array();
        foreach ($this->rs as $k=>$obj) {
            $row = $obj->rawData();
            if (!isset($fks[$row[$fk]])) {
                $fks[$row[$fk]] = array();
            }
            $fks[$row[$fk]][]= $k;
        }
        $find = $repo->findByIds(array_keys($fks));
        foreach ($find as $obj) {
            foreach ($fks[$obj->getId()] as $i) {
                $this->rs[$i]->attach($property, $obj);
            }
        }
    }
    
    function column($name) {
        $ret = array();
        foreach ($this as $obj) {
            $ret[]= $obj->get($name);
        }
        return $ret;
    }
    
    function toArray() {
        return $this->rs;
    }
}
