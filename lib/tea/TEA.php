<?php
namespace tea;
class TEA {
    static function autoload($class) {
        if (substr($class, 0, 5) == 'tea::') {
            include __DIR__.'/../'.str_replace('::', '/', $class).'.php';
        }
    }
}

spl_autoload_register(array('tea::TEA','autoload'));

