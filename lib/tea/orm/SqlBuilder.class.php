<?php

/**
 * SqlBuilder
 * Build SQL by query for specialfied database
 *
 * @author xiezhenye
 */
class SqlBuilder {
    private $conf;
    /**
     * @var DB
     */
    private $db;
    
    /**
     * @param array $conf
     * @param DB $db
     */
    function __construct($conf, $db) {
        $this->conf = $conf;
        $this->db = $db;
    }
    
    function buildSelect($query) {
        $sql = 'select '.$this->buildProperties($query['class'], $query['properties']) . 
                ' from '.$this->getTable($query['class']);
        list($condStr, $params) = $this->buildCond($query['class'], $query['condition']);
        if ($condStr != '') {
            $sql.= ' where ' . $condStr;
        }

        $sql.= $this->buildOrderBy($query);
        if (isset($query['limit'])) {
            $sql = $this->addLimitOffset($sql, $query);
        }
        return array($sql, $params);
    }

    protected function addLimitOffset($sql, $query) {
        if (is_array($query['limit'])) {
            $offset = intval(key($query['limit']));
            $limit = intval(current($query['limit']));
        } else {
            $offset = 0;
            $limit = intval($query['limit']);
        }
        return $this->db->addLimitClause($sql, $limit, $offset);
    }
    
    protected function buildOrderBy($query) {
        if (!isset($query['orderBy'])) {
            return '';
        }
        $ret = '';
        foreach ($query['orderBy'] as $property => $d) {
            $d = strtolower($d) == 'desc' ? 'desc' : 'asc';
            $ret.= $this->getField($query['class'], $property) . ' ' . $d;
        }
        return ' order by '.$ret;
    }

    function buildInsert($query) {
        $sql = 'insert into '.$this->getTable($query['class']);
        $fields = array();
        $values = array();
        
        foreach ($query['data'] as $property => $value){
            $fields[]= $this->getField($query['class'], $property);
            $values[]= $this->getValue($value);
        }
        
        $sql.= '('.implode(',',$fields).') values('.implode(',',$values).')';
        return array($sql, array());
    }
    
    function buildUpdate($query) {
        $sql = 'update '.$this->getTable($query['class']).' set ';
        $values = array();
        $items = array();
        foreach ($query['data'] as $property => $value){
            $items[]= $this->getField($query['class'], $property) . '='.$this->getValue($value);
        }
        $sql.= implode(',', $items);
        list($condStr, $params) = $this->buildCond($query['class'], $query['condition']);
        if ($condStr != '') {
            $sql.= ' where ' . $condStr;
        }
        return array($sql, $params);
    }

    function buildDelete($query) {
        $sql = 'delete from '.$this->getTable($query['class']);

        list($condStr, $params) = $this->buildCond($query['class'], $query['condition']);
        if ($condStr != '') {
            $sql.= ' where ' . $condStr;
        }
        return array($sql, $params);
    }

    protected function buildProperties($class, $properties) {
        if (is_null($properties)) {
            $properties = array_keys($this->conf[$class]['properties']);
        }
        if (is_string($properties)) {
            $properties = array_map('trim', explode(',', $properties));
        }
        $fields = array();
        foreach ($properties as $prop) {
            $fields[]= $this->getField($class, $prop) . ' ' . $this->db->addDelimiter($prop);
        }
        return implode(',', $fields);
    }
    
    protected function buildCond($class, $cond) {
        if (!is_array($cond)) {
            return array($this->buildComplexItem($class, strval($cond)), array());
        }
        
        $items = array();
        $params = array();
        foreach ($cond as $key=>$value) {
            $item = '';
            if (is_string($key)) { // 'prop'=>'value'
                $items[] = $this->getField($class, $key).'='.$this->getValue($value);
            } elseif (is_string($value)) {
                $item = $this->buildComplexItem($class, $value);
            } elseif ($this->isComplexItem($value)) {
                $item = $this->buildComplexItem($class, $value[0]);
                $params = array_merge($params, $value[1]);
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
        return array('(' . implode(') and (', $items) . ')', $params);
    }

    private function isComplexItem($item) {
        return isset($item[0]) && isset($item[1]);
    }
    
    private function _cpxCallback($m) {
        $prop = trim($m[1]);
        return $this->getField($this->_tempCurClass_, $prop);
    }
    
    private function buildComplexItem($class, $condItem) {
        if (!preg_match('/^[\[\]\w\s<=>!:\.]+$/', $condItem)) {
            return '';
        }
        $tempCurClass = '_tempCurClass_';
        $this->$tempCurClass = $class;
        return preg_replace_callback('~\[([a-z_]\w*)\]~i', array($this, '_cpxCallback'), $condItem);
    }
    
    protected function getTable($class) {
        if (isset($this->conf[$class]['table'])) {
            return $this->db->addDelimiter($this->conf[$class]['table']);
        }
        return $this->db->addDelimiter($class);
    }

    protected function getField($class, $prop) {
        if (isset($this->conf[$class]['properties'][$prop]['field'])) {
            return $this->db->addDelimiter($this->conf[$class]['properties'][$prop]['field']);
        } else {
            return $this->db->addDelimiter($prop);
        }
    }

    protected function getValue($value, $type = null) {
        if (is_string($value)) { // string value
            return $this->db->quote($value);
        } elseif (is_bool($value)) { // convert boolean to int
            return intval($value);
        } elseif (is_scalar($value)) { // int, float
            return $value;
        } elseif (is_array($value)) {
            $items = array();
            foreach($value as $v){
                if (is_scalar($v)){
                    $items[]= $this->getValue($v);
                }
            }
            return '('.implode(',',$items).')';
        }
        return "''";
    }

}


