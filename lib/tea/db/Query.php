<?php
namespace tea::db;

class Query {
    function __construct($cond = null) {
        if (!empty($cond)) {
            $this->add($cond);
        }
    }

    function add($cond) {
        $conds = array();
        
    }
    
}
