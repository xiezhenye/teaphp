<?php
/**
 *
 * @package util
 */
class Annotation {
    /**
     *
     * @return array
     */
    public static function getClassAnnotations($class) {
        static $cache = array();
        if (!isset($cache[$class])) {
            $rfl = new ReflectionClass($class);
            $cmt = $rfl->getDocComment();
            $cache[$class] = self::parseComment($cmt);
        }
        return $cache[$class];
    }
    /**
     *
     * @return array
     */
    public static function getMethodAnnotations($class, $method) {
        static $cache = array();
        if (!isset($cache[$class][$method])) {
            $rfl = new ReflectionMethod($class, $method);
            $cmt = $rfl->getDocComment();
            $cache[$class][$method] = self::parseComment($cmt);
        }
        return $cache[$class][$method];
    }
    /**
     *
     * @return array
     */
    public static function getPropertyAnnotations($class, $property) {
        static $cache = array();
        if (!isset($cache[$class][$property])) {
            $rfl = new ReflectionProperty($class, $property);
            $cmt = $rfl->getDocComment();
            $cache[$class][$property] = self::parseComment($cmt);
        }
        return $cache[$class][$property];
    }
    
    /**
     *
     * @return array
     */
    static function parseComment($cmt) {
        if (!preg_match_all('~^\s*\*?\s*@(\S+)(?:[ \t]+(.*))?~Sm', $cmt, $m)) {
            return array();
        }
        $ret = array();
        foreach ($m[1] as $k=>$v) {
            $ret[$v] = $m[2][$k];
        }
        return $ret;
    }
}
