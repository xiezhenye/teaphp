<?php

class ActiveRecord implements ArrayAccess {
    protected $_row;
    private static $_conf = array();

    function __construct($row) {
        $this->_row = $row;
    }

    function __get($name) {
        return $this->_row[$name];
    }

    function __set($name, $value) {
        $this->_row[$name] = $value;
    }

    static function init($conf) {
        $class = get_called_class();
        self::$_conf[$class] = $conf;
    }

    static function find($query) {
        
    }

    function offsetExists($name) {
        
    }

    function offsetSet($name, $value) {
        
    }

    function offsetGet($name) {
        
    }

    function offsetUnset($name) {
        
    }
}

