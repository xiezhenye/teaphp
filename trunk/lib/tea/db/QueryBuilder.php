<?php
namespace tea::db;

class QueryBuilder {
    private $conf;
    private $db;

    function __construct($conf, $db) {
        $this->conf = $conf;
        $this->db = $db;
    }
    
    function build($query) {
        $sql = 'select '.$this->buildProperties($query['properties']) . 
                ' from '.$this->getTable($query['class']);
        $condStr = $this->buildCond($query['class'], $query['condition']);
        if ($condStr != '') {
            $sql.= ' where ' . $condStr;
        }
        return $sql;
    }
    
    function buildProperties($properties) {
        if (is_null($properties)) {
            return '*';
        } elseif (is_array($properties)) {
            return implode(',', array_map(array($this->db, 'addDelimiter'), $properties));
        } elseif (is_string($properties)) {
            $props = array_map('trim', explode(',', $properties));
            return implode(',', array_map(array($this->db, 'addDelimiter'), $props));
        } else {
            
        }
    }
    
    function buildCond($class, $cond) {
        if (!is_array($cond))
            return '';

        if ($this->isComplexItem($cond)) {
            return $this->buildComplexItem($class, $cond);   
        }

        $items = array();
        foreach ($cond as $key=>$value) {
            if (is_string($key)) {
                $items[] = $this->getField($key).'='.$this->getValue($value);
            } elseif ($this->isComplexItem($value)) {
                $item = $this->buildComplexItem($class, $value);
                if ($item != '') {
                    $items[]= $item;
                }
            }
        }
        if (empty($items)) {
            return '';
        }
        return '(' . implode(') and (', $items) . ')';
    }
    
    private function isComplexItem($item) {
        return count($item) == 2 &&
                isset($item[0]) && isset($item[1])
               && is_string($item[0]) && is_array($item[1]);
    }
    
    private function _cpxCallback($m) {
        return $this->getField($m[0]);
    }

    private function buildComplexItem($class, $condItem) {
        if (!preg_match('/^[\w\s<=>!:]+$/', $condItem[0])) {
            return '';
        }
        //echo preg_replace_callback('/[a-z]\w*/i', array($this, '_cpxCallback'), $condItem[0]);

        $tokens = token_get_all('<?php '.$condItem[0]);
        $item = '';
        for ($i = 1; $i < count($tokens); $i++) {
            if ($tokens[$i] == ':' && is_array($tokens[$i+1]) && $tokens[$i+1][0] == T_STRING) {
                $item.= $this->getValue($condItem[1][$tokens[$i+1][1]]);
                $i++;
            } elseif (is_array($tokens[$i])){
                if ($tokens[$i][0] == T_STRING) {
                    $item.=$this->getField($class, $tokens[$i][1]);
                } else {
                    $item.= $tokens[$i][1];
                }
            } else {
                $item.= $tokens[$i];
            }
        }
        return $item;
    }
    
    protected function getTable($class) {
        if (isset($this->conf[$class]['table'])) {
            return $this->db->addDelimiter($this->conf[$class]['table']);
        }
        return $this->db->addDelimiter($class);
    }

    protected function getField($class, $prop) {
        if (isset($this->conf[$class]['properties'][$prop])) {
             return $this->db->addDelimiter($prop);
        } else {
            return $prop;
        }
    }

    protected function getValue($value) {
        if (is_string($value)) {
            return "'".$this->db->quote($value)."'";
        } elseif (is_bool($value)) {
            return intval($value);
        } elseif (is_scalar($value)) {
            return $value;
        } elseif (is_array($value)) {
            $items = array();
            foreach($value as $v){
                if (is_scalar($v)){
                    $items[]= $this->getValue($v);
                }
            }
            return '('.implode(',',$items).')';
        }
        return "''";
    }
}

