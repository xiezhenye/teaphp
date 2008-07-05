<?php
namespace tea::common;

/**
 * Date
 * 
 * to provide a common date time type
 * 
 * @author xiezhenye<xiezhenye@gmail.com>
 */
class Date {
    private $timestamp;
    
    public function __construct($d = null) {
        if (is_null($d)){
            $this->timestamp = time();
            return;
        }
        if (is_int($d)){
            $this->timestamp = $d;
            return;
        }
        if (is_string($d)){
            $this->timestamp = strtotime($d);
            return;
        }
        if ($d instanceof tea::common::Date) {
            $this->timestamp = $d->timestamp;
            return;
        }
        throw new Exception('Invalid construct parameter');
    }

    public function getMicrotime() {
        return microtime(true);
    }
    
    /**
     * convert to string
     * @return string
     */
    public function __toString() {
        return date('Y-m-d H:i:s', $this->timestamp);
    }

    public function format($pattern) {
        return date($pattern, $this->timestamp);
    }
    
    
    public function parse($string, $d = null) {
        $date = new Date($d);
        $this->timestamp = strtotime($string, $date->timestamp);
        return $this;
    }
}

