<?php

class Validator {
    static function assertNotEmpty($v, $msg, $type = BaseView::FAILURE) {
        self::assert(!empty($v), $msg, $type);
    }
    
    static function assertNotEmptyString($v, $msg, $type = BaseView::FAILURE) {
        self::assert(!preg_match('/^\s*$/', $v), $msg, $type);
    }
    
    static function assertIsPositiveInterger($v, $msg, $type = BaseView::FAILURE) {
        self::assert(preg_match('/^\d+$/', $v), $msg, $type);
    }
    
    static function assertIsEmail($v, $msg, $type = BaseView::FAILURE) {
        self::assert(StringUtil::isEmail($v), $msg, $type);
    }
    
    static function assertIsUrl($v, $msg, $type = BaseView::FAILURE) {
        self::assert(StringUtil::isUrl($v), $msg, $type);
    }
    
    static function assert($bool, $msg, $type = BaseView::FAILURE) {
        if (! $bool) {
            throw new ActionException($msg, $type);
        }
    }
    
    static function assertNot($bool, $msg, $type = BaseView::FAILURE) {
        self::assert(!$bool, $msg, $type);
    }
    
    static function assertEquals($a, $b, $msg, $type = BaseView::FAILURE) {
        self::assert($a == $b, $msg, $type);
    }
    
    static function assertNotEquals($a, $b, $msg, $type = BaseView::FAILURE) {
        self::assert($a != $b, $msg, $type);
    }
    
    static function assertRegexMatch($v, $regex, $msg, $type = BaseView::FAILURE) {
        self::assert(preg_match($regex, $v), $msg, $type);
    }
}
