<?php
class DateTimeUtil {
    const PATTERN_DATE = 'Y-m-d';
    const PATTERN_DATE_TIME = 'Y-m-d H:i:s';
    const SECONDS_PER_DAY = 86400;
    
    //static $default_pattern = self::PATTERN_DATE_TIME;
    
    static function today($pattern = self::PATTERN_DATE) {
        return self::changeFormat('today', $pattern);
    }
    
    static function tomorrow($pattern = self::PATTERN_DATE) {
        return self::changeFormat('tomorrow', $pattern);
    }
    
    static function yesterday($pattern = self::PATTERN_DATE) {
        return self::changeFormat('yesterday', $pattern);
    }
    
    static function offsetSeconds($date1, $date2) {
        return strtotime($date1) - strtotime($date2);
    }
    
    static function offsetDays($date1, $date2) {
        return self::offsetSeconds($date1, $date2) / self::SECONDS_PER_DAY;
    }
    
    static function changeFormat($date, $pattern = self::PATTERN_DATE_TIME) {
        return date($pattern, strtotime($date));
    }
    
    static function now($pattern = self::PATTERN_DATE_TIME) {
        return date($pattern);
    }
    
    
}

