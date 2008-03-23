<?php
namespace tea::common;
class Config implements ArrayAccess {
    private $conf = array();

    function __construct($conf, $type = null) {
        if (is_string($conf)) {
            $this->load($conf, $type);
        }

        if (is_array($conf)) {
            $this->conf = $conf;
        }
    }
    
    public function load($confFile, $type = null) {
        $confType = strval($type);
        if (is_null($type)) {
            $confType = $this->getTypeByExt($confFile);
        }
        switch ($confType) {
            case 'php':
                $this->loadPHP($confFile);
                break;
            case 'js':
                $this->loadJSON($confFile);
                break;
            default:
                break;
        }
    }
    
    public function loadPHP($confFile) {
        $conf = include($confFile);
        if (is_array($conf)) {
            $this->conf = $conf;
        }
    }

    public function loadJSON($confFile) {
        $s = file_get_contents($confFile);
        $this->conf = json_decode($s, true);
    }

    public function getTypeByExt($path) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, array('php','js'))) {
            return $ext;
        }
        return null;
    }

    public function getArray() {
        return $this->conf;
    }

    public function offsetExists($key) {
        return isset($this->conf[$key]);
    }
    
    public function offsetGet($key) {
        return $this->conf[$key];
    }

    public function offsetSet($key, $value) {
        throw new Exception('can not set');
    }

    public function offsetUnset($key) {
        throw new Exception('can not unset');
    }
    
}

