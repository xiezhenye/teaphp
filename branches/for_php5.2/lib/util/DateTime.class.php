<?php
class DateTime {
    protected $time;
    
    function __construct($time = null) {
        if (is_null($time)) {
            $this->time = time();
            return;
        }
        if (is_int($time)) {
            $this->time = $time;
            return;
        }
        $this->time = strtotime(strval($time));
    }
    
    static function now() {
        return new DateTime();
    }
    
    static function fromStr($str) {
        $this->time = strtotime($str);
    }
    
    function __toString() {
        return $this->toStr();
    }
    
    function toStr($pattern = 'Y-m-d H:i:s') {
        return date($pattern, $this->time);
    }
    
    function date() {
        return date('Y-m-d', $this->time);
    }
    
    function time() {
        return date('H:i:s', $this->time);
    }
    
    function years() {
        return intval(date('Y', $this->time));
    }
    
    function months() {
        return intval(date('m', $this->time));
    }
    
    function days() {
        return intval(date('d', $this->time));
    }

    function hours() {
        return intval(date('H', $this->time));
    }
    
    function minutes() {
        return intval(date('i', $this->time));
    }
    
    function seconds() {
        return intval(date('s', $this->time));
    }
    
}
