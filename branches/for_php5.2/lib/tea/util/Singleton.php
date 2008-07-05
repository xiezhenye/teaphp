<?php
namespace tea::util;

abstract class Singleton {
    final static function getInstance() {
        static $_instances = array();
        $class = get_called_class();
        if (is_object($_instances[$class])) {
            return $_instances[$class];
        }
        $rfc = new ReflectionClass($class);
        if ($rfc->getConstructor()) {
            $args = func_get_args();
            $_instances[$class] = call_user_func_array(array($rfc, 'newInstance'), $args);
        } else {
            $_instances[$class] = new $class();
        }
        return $_instances[$class];
    }
}



