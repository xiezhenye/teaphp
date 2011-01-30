<?php
/**
 * 记录对象
 *
 */
class BaseModel {
    protected $row;
    protected $id;
    protected $attaches = array();
    
    function __construct($row, $id) {
        $this->row = $row;
        $this->id = (array) $id;
    }
    
	/**
	 * 得到主键值
	 * @return mixed
	 */
    function getId() {
        $ret = array();
        foreach ((array) $this->id as $id) {
            $ret[]= $this->row[$id];
        }
        if (count($ret) == 1) {
            $ret = current($ret);
        }
        return $ret;
    }
	
	function idProperty() {
		return count($this->id) > 1 ? $this->id : current($this->id);
	}
    
    function set($name, $value) {
        return call_user_func(array($this, 'set'.ucfirst($name)), $value);
    }
    
    function get($name) {
        return call_user_func(array($this, 'get'.ucfirst($name)));
    }
	
	/**
	 * 原始数组
	 * @return array
	 */
    function rawData() {
        $ret = $this->row;
        /*foreach ($this->attaches as $name => $attach) {
            $ret[$name] = $attach instanceof Record ? $attach->rawData() : $attach;
        }*/
        return $ret;
    }
    
	/**
	 * 自动属性访问 getXxxx setXxxx
	 *
	 */
    function __call($name, $args) {
        $prefix = substr($name, 0, 3);
        if ($prefix == 'set') {
            $property = substr($name, 3);
            $property[0] = strtolower($property[0]);
            $this->row[$property] = $args[0];
        }
        if ($prefix == 'get') {
            $property = substr($name, 3);
            $property[0] = strtolower($property[0]);
            if (isset($this->row[$property])) {
                return $this->row[$property];
            }
            if (isset($this->attaches[$property])) {
                return $this->attaches[$property];
            }
			return NullObject::getInstance();
        }
    }
    
    /**
     * 附加一个从属对象
     * 
     * @param string $property
     * @param mixed $obj
     */
    function attach($property, $obj = null) {
        $this->attaches[$property] = $obj;
    }
	
    function allAttaches() {
        return $this->attaches;
    } 
}

