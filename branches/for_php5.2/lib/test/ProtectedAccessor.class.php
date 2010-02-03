<?php
/**
 * ProtectedAccessor
 *
 * ProtectedAccessor 提供了访问对象protected属性的途径。
 *
 * @copyright Copyright Zhenye Xie 2006. All rights reserved.
 * @author Zhenye Xie <xiezhenye@gmail.com>
 * @version 0.8
 * @package common
 */
class ProtectedAccessor {
    private static $classes=array();

    private static function generateAccessor($class){
        $accClass=self::getAccessorClassName($class);
        if(!class_exists($accClass)){
            $code='class '.$accClass.' extends '.$class.' { ';
            $code.='static function &_tea_get_var_($obj,$var){return $obj->$var;}';
            $code.='static function _tea_set_var_($obj,$var,$value){$obj->$var=$value;}';
            $code.='static function _tea_call_($obj,$method,&$args){return call_user_func_array(array($obj,$method),$args);}';
            $code.='}';
            eval($code);
        }
    }

    static function &get($object, $var){
        $class=get_class($object);
        self::generateAccessor($class);
        $ret=call_user_func(array(self::getAccessorClassName($class),
            '_tea_get_var_'),$object,$var);
        return $ret;
    }

    static function set($object, $var, $value){
        $class=get_class($object);
        self::generateAccessor($class);
        $ret=call_user_func(array(self::getAccessorClassName($class),
            '_tea_set_var_'),$object,$var,$value);
        return $ret;
    }
    
    static function &call($object, $method, $args) {
        $class=get_class($object);
        self::generateAccessor($class);
        $ret=call_user_func(array(self::getAccessorClassName($class),
            '_tea_call_'),$object,$method,$args);
        return $ret;
    }

    private static function getAccessorClassName($className){
        return '__Protected_Accessor__'.$className;
    }
}





