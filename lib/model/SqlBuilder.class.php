<?php

/**
 * SqlBuilder
 * 从 Query 对象构造 SQL 语句
 * @package model
 * @author xiezhenye
 */
class SqlBuilder {
    private $conf;
    private $className;
    /**
     * @var DB
     */
    private $db;
    
    private $tablePrefix = '';
    
    private $tableSurfix = '';
    private $_propertiesRegEx;
    /**
     * @param array $conf
     * @param DB $db
     */
    function __construct($conf, $db) {
        $this->conf = $conf;
        $this->db = $db;
    }
    
    /**
     * 设置表前缀
     *
     * @param string $prefix
     */
    function setTablePrefix($prefix) {
        $this->tablePrefix = $prefix;
    }
    
    /**
     * 设置表后缀
     *
     * @param string $surfix
     */
    function setTableSurfix($surfix) {
        $this->tableSurfix = $surfix;
    }
    
    /**
     * 构造select语句
     *
     * @param array $query
     * @return string
     */
    function buildSelect($query) {
	$sql = 'select '.$this->buildProperties($query['properties']) .
                $this->buildForignKeys() . 
                ' from '.$this->getTable($query['class']);
        $condStr = $this->buildCond($query['condition']);
	
        if ($condStr != '') {
            $sql.= ' where ' . $condStr;
        }

        $sql.= $this->buildOrderBy($query);
        if (isset($query['limit'])) {
            $sql = $this->addLimitOffset($sql, $query);
        }
        return $sql;
    }
    
    /**
     * 构造insert语句
     *
     * @param array $query
     * @return string
     */
    function buildInsert($query) {
        $sql = 'insert into '.$this->getTable($query['class']);
        $fields = array();
        $values = array();
        foreach ($query['data'] as $property => $value){
            if (is_array($value)) {
                throw new Exception('value can not be array');
            }
            $fields[]= $this->getField($property);
            $values[]= $this->getValue($value, $this->getFieldType($property));
        }
        $sql.= '('.implode(',',$fields).') values('.implode(',',$values).')';
        return $sql;
    }
    
    /**
     * 构造update语句
     *
     * @param array $query
     * @return string
     */
    function buildUpdate($query) {
        $sql = 'update '.$this->getTable($query['class']).' set ';
        $values = array();
        $items = array();
        foreach ($query['data'] as $property => $value){
            if (is_array($value)) {
                throw new Exception('value can not be array');
            }
            $items[]= $this->getField($property) . '='.
                $this->getValue($value, $this->getFieldType($property));
        }
        $sql.= implode(',', $items);
        $condStr = $this->buildCond($query['condition']);
        if ($condStr != '') {
            $sql.= ' where ' . $condStr;
        }
        return $sql;
    }
    
    /**
     * 构造delete语句
     *
     * @param array $query
     * @return string
     */
    function buildDelete($query) {
        $sql = 'delete from '.$this->getTable($query['class']);
        $condStr = $this->buildCond($query['condition']);
        if ($condStr != '') {
            $sql.= ' where ' . $condStr;
        }
        return $sql;
    }
    
    /**
     *
     *
     */
    function parseParams($sql, $params) {
        $tr = array();
        foreach ($params as $k=>$v) {
            $tr[':'.$k] = $this->getValue($v, 'string');
        }
        $parsed = strtr($sql, $tr);
        return $parsed;
    }
    
    public function addLimitOffset($sql, $query) {
        if (is_array($query['limit'])) {
            $offset = intval(key($query['limit']));
            $limit = intval(current($query['limit']));
        } else {
            $offset = 0;
            $limit = intval($query['limit']);
        }
        return $this->db->addLimitClause($sql, $limit, $offset);
    }
    
    public function buildOrderBy($query) {
        if (!isset($query['orderBy'])) {
            return '';
        }
        $ret = array();
        foreach ($query['orderBy'] as $property => $d) {
            $d = strtolower($d) == 'desc' ? 'desc' : 'asc';
            $ret[]= $this->getField($property) . ' ' . $d;
        }
        return ' order by '.implode(',', $ret);
    }
    
    public function buildProperties($properties) {
	if (is_null($properties)) {
            $properties = array_keys($this->conf['properties']);
        }
        if (is_string($properties)) {
            $properties = array_map('trim', explode(',', $properties));
        }
        $fields = array();
        foreach ($properties as $prop) {
            $fields[]= $this->getField($prop) . ' ' . $this->db->addDelimiter($prop);
        }
        return implode(',', $fields);
    }
    
    public function buildForignKeys() {
        if (!isset($this->conf['hasOne'])) {
            return '';
        }
        $fks = array();
        foreach ($this->conf['hasOne'] as $v) {
            $fks[]= $this->db->addDelimiter($v['field']);
        }
        return ','.implode(',', $fks);
    }
    
    public function buildCond($cond) {
        if (!is_array($cond)) {
            return $this->buildComplexItem(strval($cond));
        }
        
        $items = array();
        foreach ($cond as $key=>$value) {
            $item = '';
            if (is_string($key)) { // 'prop'=>'value'
				$field = $this->getField($key);
                if (is_array($value)) {
                    if (empty($value)) {
                        $item = '1 = 0';
					} else {
						$value = $this->getValue($value, $this->getFieldType($key));
                        $item = $field.' in '.$value;
                    }
				} elseif (is_object($value) && $value->type == 'between') {
					$item = $this->buildRange($key, $value);
                } else {
                    $item = $field.'='.$this->getValue($value, $this->getFieldType($key));
                }
            } elseif (is_string($value)) { // 字符串表达式
                $item = $this->buildComplexItem($value);
            } elseif (is_array($value) && count($value) == 2) { // 用于复合属性
                $item = $this->buildCond($this->getPropertyCond($value[0], $value[1]));
            } else {
                continue;
            }
            if ($item != '') {
                $items[]= $item;
            }
        }
        if (empty($items)) {
            return '';
        }
        return '(' . implode(') and (', $items) . ')';
    }
        
    private function _cpxCallback($m) {
        $prop = trim($m[1]);
        return $this->getField($this->_tempCurClass_, $prop);
    }
    
	public function buildRange($prop, $range) {
		$field = $this->getField($prop);
		$item = $field . ($range->include_from ? '>=' : '>') .
				$this->getValue($range->from) .
				' and '.
				$field . ($range->include_to ? '<=' : '<') .
				$this->getValue($range->to);
		return $item;
	}
	
    public function buildComplexItem($condItem) {
        if (is_null($this->_propertiesRegEx)) {
            $find = array();
            $replace = array();
            foreach ($this->conf['properties'] as $k=>$v) {
                $chars = '[\s\(\),+\-*/<=>&\|]';
                $find[]= "~($chars+)$k($chars+)~";
                $replace[]= '$1'.$this->getField($k).'$2';
            }
            $this->_propertiesRegEx = array($find, $replace);
        }
        $ret = preg_replace($this->_propertiesRegEx[0], $this->_propertiesRegEx[1], " $condItem ");
        return $ret;
    }
    
    public function getTable($class) {
        if (isset($this->conf['table'])) {
            return $this->db->addDelimiter($this->tablePrefix.$this->conf['table'].$this->tableSurfix);
        }
        return $this->db->addDelimiter($this->tablePrefix.$class.$this->tableSurfix);
    }

    public function getField($prop) {
        $prop = trim($prop);
        if (preg_match('~^([a-z]\w*\()(\w+|\*)(\))$~i', $prop, $m)) {
            $pre = $m[1];
            $post = $m[3];
            $prop = $m[2];
        } else {
            $pre = $post = "";
            $pattern = "%s";
        }
        if (isset($this->conf['properties'][$prop]['field'])) {
	    $field = $this->db->addDelimiter($this->conf['properties'][$prop]['field']);  
        } elseif ($prop == '*') {
            $field = $prop;
        } else {
            $field = $this->db->addDelimiter($prop);
        }
        return $pre . $field . $post;
    }
    
    function getPropertyCond($prop, $value) {
        if (!is_array($prop)) {
            return array($prop=>$value);
        }
        if (is_null($value)) {
            return array('0=1');
        }
        $excpt = 'the value not match the properties';
        if (!is_array($value)) {
            throw new Exception($excpt);
        }
        $str_props = '('.implode(',', $prop).')';
        if (!is_array($value[0])) {
            if (count($value) != count($prop)) {
                throw new Exception($excpt);
            }
            $op = ' = ';
        } else {
            if (count($value[0]) != count($prop)) {
                throw new Exception($excpt);
            }
            $op = ' in ';
        }

        $cond = $str_props . $op . $this->getValue($value);
        return $cond;
    }
        
    protected function getFieldType($prop) {
        $ret = isset($this->conf['properties'][$prop]['type']) ?
            $this->conf['properties'][$prop]['type'] :
            null;
        return $ret;
    }

    public function getValue($value, $type = null) {
        if (is_array($value)) {
            $items = array();
            foreach($value as $v){
                //if (is_scalar($v)){
                    $items[]= $this->getValue($v, $type);
                //}
            }
            return '('.implode(',',$items).')';
        }
        return "'".$this->db->quote(strval($value))."'"; // no type guess
        
        if (is_null($type)) {
            return $this->autoConvert($value);
        }
        $type = strtolower($type);
        switch ($type) {
        case 'bool':
        case 'boolean':
            return intval($value);
        case 'int':
        case 'integer':
        case 'float':
        case 'double':
            return $value;
        case 'string':
        default:
            return "'".$this->db->quote(strval($value))."'";
        }
    }
    
    protected function autoConvert($value) {
        if (is_string($value)) { // string value
            return "'".$this->db->quote($value)."'";
        } elseif (is_bool($value)) { // convert boolean to int
            return intval($value);
        } elseif (is_scalar($value)) { // int, float
            return $value;
        } else {
            return "'".$this->db->quote(strval($value))."'";
        }
        return "''";
    }

}


