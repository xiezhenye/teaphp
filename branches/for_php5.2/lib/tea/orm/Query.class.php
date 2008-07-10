<?php

class Query {
    /**
     * @var SqlBuilder
     */
    protected $sqlBuilder;
    
    /**
     * @var DB
     */
    protected $db;
    protected $conf;
    protected $class;
    protected $query = array();
    
    /**
     * @param string $class
     * @param array $conf
     * @param DB $db
     */
    function __construct($class, $conf, $db) {
        $this->sqlBuilder = new SqlBuilder($conf, $db);
        $this->db = $db;
        $this->conf = $conf;
        $this->className = $class;
        $this->query['class'] = $class;
    }
    /**
     * @return Query
     */
    function select($properties) {
        $this->query['properties'] = $properties;
        return $this;
    }
    
    /**
     * @return Query
     */
    function where($cond) {
        $this->query['condition'] = $cond;
        return $this;     
    }
    /**
     * @return Query
     */
    function limit($a) {
        $this->query['limit'] = $a;
        return $this;
    }
    /**
     * @return Query
     */
    function orderBy($o) {
        $this->query['orderBy']= $o;
        return $this;
    }
    
    /**
     * @return PreparedStatement
     */
    function getStatement() {
        if (empty($this->query['properties'])) {
            $properties = $this->conf[$this->className]['properties'];
            $this->query['properties'] = array_keys($properties);
        }
        list($sql, $param) = $this->sqlBuilder->buildSelect($this->query);
        $ret = $this->db->prepare($sql);
        foreach ($param as $k=>$v) {
            $ret->bind($k, $v);
        }
        return $ret;
    }
    
    function execute($param = array()) {
        return $this->getStatement()->execute($param);
    }
}


