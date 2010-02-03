<?php
class Stdio {
    private static $stdin = null;
    private static $stdout = null;
    private static $stderr = null;
    
    static function getStdout() {
        if (is_null(self::$stdout)) {
            self::$stdout = fopen('php://stdout', 'ab');
        }
        return self::$stdout;
    }
    
    static function getStdin() {
        if (is_null(self::$stdin)) {
            self::$stdin = fopen('php://stdin', 'rb');
        }
        return self::$stdin;
    }
    
    static function getStderr() {
        if (is_null(self::$stderr)) {
            self::$stderr = fopen('php://stderr', 'rb');
        }
        return self::$stderr;
    }
    
    static function setStderr($fp) {
        self::$stderr = $fp;
    }
    
    static function setStdout($fp) {
        self::$stdout = $fp;
    }
    
    static function setStdin($fp) {
        self::$stdin = $fp;
    }
    
    static function gets($size = 0) {
        return fgets(self::getStdin(), $size);
    }
    
    static function getc() {
        return fgetc(self::getStdin());
    }
    
    static function readLine($size = 0) {
        return self::gets($size);
    }
    
    static function read($size) {
        return fread(self::getStdin(), $size);
    }
    
    static function write($s) {
        fwrite(self::getStdout(), $s);
    }
    
    static function println($s) {
        fwrite(self::getStdout(), $s."\n");
    }
    
    static function errPrintln($s) {
        self::errWrite($s."\n");
    }
    
    static function errWrite($s) {
        fwrite(self::getStderr(), $s);
    }
    
    static function scanf() {
        if (func_num_args() < 1) {
            throw new Exception('argument not enough');
        }
        $args = func_get_args();
        array_unshift($args, self::getStdin());
        call_user_func_array('fscanf', $args);
    }
    
    static function printf() {
        if (func_num_args() < 1) {
            throw new Exception('argument not enough');
        }
        $args = func_get_args();
        array_unshift($args, self::getStdout());
        call_user_func_array('fprintf', $args);
    }
    
    static function errPrintf() {
        if (func_num_args() < 1) {
            throw new Exception('argument not enough');
        }
        $args = func_get_args();
        array_unshift($args, self::getStderr());
        call_user_func_array('fprintf', $args);
    }
}
