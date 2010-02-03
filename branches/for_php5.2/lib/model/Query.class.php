<?php
/**
 * 查询构造器
 *
 */
class Query {
    protected $query = array('condition'=>array());
    
    function __construct($cond = null) {
        if (!is_null($cond)) {
            $this->where($cond);
        }
    }
    
    static function create() {
        return new self;
    }
    
    /**
     * 查询哪个类的对象
     *
     * @param string $class
     * @deprecated
     * @return Query
     */
    function from($class) {
        $this->query['class'] = $class;
        return $this;
    }
    
    /**
     * 选择哪些属性
     * 
     * @return Query
     */
    function select($properties) {
        $this->query['properties'] = $properties;
        return $this;
    }
    
    /**
     * 查询的条件部分
     *
     * @param mixed $cond 条件，可以是数组或字符串
     * @return Query
     */
    function where($cond) {
        $this->query['condition'] = array_merge($this->query['condition'], (array)$cond);
        return $this;     
    }
    
    /**
     * 限制结果个数
     *
     * @return Query
     */
    function limit($a) {
        $this->query['limit'] = $a;
        return $this;
    }
    
    /**
     * 排序
     * @return Query
     */
    function orderBy($o) {
        $this->query['orderBy']= $o;
        return $this;
    }
    
    /**
     * 得到构造好的查询数组
     *
     * @deprecated
     * @return array
     */
    function getQuery() {
        return $this->getArray();
    }
    
    /**
     * 得到构造好的查询数组
     * 
     * @return array
     */
    function getArray() {
        return $this->query;
    }
    
    /**
     * 分页
     * 不可与 limit 同时使用
     * 
     * @param int $page 页码
     * @param int $pageSize 每页的对象数量
     */
    function page($page, $pageSize) {
        $offset = (($page > 1) ? $page - 1 : 0) * $pageSize;
        return $this->limit(array($offset => $pageSize));
    }
}

