<?php

class ClassLoader {
    private $_baseDir = '';
    private $_ext;
    
    function __construct($baseDir, $ext = '.class.php') {
        $this->_baseDir = $baseDir;
        $this->_ext = $ext;
    }
    
    function load($class) {
        $className = substr(strrchr ($class, '/'), 1);
        if (class_exists($className)) {
            return true;
        }
        require $this->_baseDir.DIRECTORY_SEPARATOR.$class.$this->_ext;
        return true;
    }
}
