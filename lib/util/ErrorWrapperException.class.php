<?php
class ErrorWrapperException extends Exception {
    private static $binded = false;
    function __construct($errno, $errstr, $errfile, $errline) {
        $this->code = $errno;
        $this->message = $errstr;
        $this->file = $errfile;
        $this->line = $errline;
    }
    
    static function errorHandler($errno, $errstr, $errfile, $errline) {
        throw new ErrorWrapperException($errno, $errstr, $errfile, $errline);
    }
    
    static function bind() {
        if (self::$binded) {
            return;
        }
        $types = E_ALL & ~E_NOTICE & ~E_STRICT;
        set_error_handler(array(__CLASS__, 'errorHandler'), $types);
        self::$binded = true;
    }
}

