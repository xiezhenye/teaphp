<?php
/**
 * 通用 NullObject，
 * 对属性读取，方法调用返回 NullObject，count()返回 0，
 * 对属性 is_set 返回 false
 * __toString 返回 ''
 * @package util
 */
class NullObject implements Countable,ArrayAccess {
    static private $instance = null;
    private function __construct() {
        	
    }

    static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new NullObject;
        }
        return self::$instance;
    }

    function __get($name) {
        return $this;
    }
    
    function __set($name, $value) {
        return null;
    }
    
    function __call($name, $args) {
        return $this;
    }
    
    function __toString() {
        return '';
    }
    
    function isEmpty() {
        return true;
    }
    
    function count() {
        return 0;
    }
    
    function __isset($name) {
        return false;
    }
    
    function offsetExists($k) {
        return false;
    }
    
    function offsetGet($k) {
        return $this;
    }
    
    function offsetSet($k,$v) {
    }
    
    function offsetUnset($k) {
    }
}

