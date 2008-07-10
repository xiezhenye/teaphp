<?php

namespace tea::orm;

class Record {
    protected $_row;

    function __construct($row) {
        $this->_row = $row;
    }

    function __get($name) {
        return $_row[$name];
    }

    function __set($name, $value) {
        $this->_row[$name] = $value;
    }
}

