<?php
namespace tea::util;

class ClassLoader {
    private $_baseNamespace = '';
    private $_baseDir = '';
    private $_ext;
    function __construct($baseNamespace, $baseDir, $ext = '.php') {
        $this->_baseDir = $baseDir;
        $this->_baseNamespace = $baseNamespace;
        $this->_ext = $ext;
    }
    
    function load($class) {
        if (substr($class, 0, strlen($this->_baseNamespace)) != $this->_baseNamespace) {
            return false;
        }
        $classPostfix = substr($class, strlen($this->_baseNamespace) + 2);
        $path = str_replace('::', DIRECTORY_SEPARATOR, $classPostfix).$this->_ext;
        require $this->_baseDir . DIRECTORY_SEPARATOR . $path;
        return true;
    }

    function register() {
        spl_autoload_register(array($this, 'load'));
    }

    function unregister() {
        spl_autoload_unregister(array($this, 'load'));
    }
}
