<?php
namespace tea;

class TEA {
    static function init() {
        require_once __DIR__.'/util/ClassLoader.php';
        $loader = new tea::util::ClassLoader('tea', __DIR__);
        $loader->register();
    }
}

TEA::init();


