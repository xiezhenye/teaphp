<?php
/**
 * 对象仓储，提供对象查找保存更新删除功能
 *
 */
class Repository {
    protected static $isNew = array();
    
    protected $className;
    protected $conf;
    /**
     * @var Mysql
     */
    protected $db;
    
    /**
     * @var SqlBuilder
     */
    protected $sqlBuilder;
    
    protected $beforeAddCallbacks = array();
    protected $beforeDeleteCallbacks = array();
    protected $beforeUpdateCallbacks = array();
    protected $beforeFindCallbacks = array();
    protected $beforeSaveCallbacks = array();
    
    protected $afterAddCallbacks = array();
    protected $afterDeleteCallbacks = array();
    protected $afterUpdateCallbacks = array();
    protected $afterFindCallbacks = array();
    protected $afterSaveCallbacks = array();
    
    /**
     * 得到数据库对象
     * 
     * @return DB
     */
    function getDB($q = null) {
        return $this->db;
    }
    
    /**
     * 得到类名
     * 
     * @return string
     */
    function getClass() {
        return $this->className;
    }
    
    /**
     * 设置类名
     * 
     * @param string $className
     */
    function setClass($className) {
        $this->className = $className;
    }
    
    /**
     * 设置配置
     * @param array $conf
     */
    function setConfig($conf) {
        $this->conf = $conf;
    }
    
    /**
     * 设置数据库对象
     *
     * @param Mysql $db
     */
    function setDB($db) {
        $this->db = $db;
    }
    
    /**
     * 得到 Sql 语句构造器
     * @return SqlBuilder
     */
    function getSqlBuilder($q = null) {
        if (empty($this->sqlBuilder)) {
            $this->sqlBuilder = new SqlBuilder($this->conf, $this->getDB($q));
        }
        return $this->sqlBuilder;
    }
    
    /**
     * 得到查询对象
     * 
     * @return Query
     * @deprecated
     */
    function getQuery() {
        $query = new Query();
        $query->from($this->className);
        return $query;
    }
    
    function attachEvent($event, $callback) {
        $event_prop = $event . 'Callbacks';
        if (!isset($this->$event_prop) || !is_callable($callback)) {
            return false;
        }
        array_push($this->$event_prop, $callback);
    }
    
    /**
     *
     * @param int $page_no
     * @param int $page_size
     * @param Query $query
     * @param array $params
     */
    function findPage($page_no, $page_size, $query, $params = array()) {
        $q = $query->getArray();
        $cond = $q['condition'];
        $count = $this->count($cond, $params);
        if ($page_no < 1) {
            $page_no = 1;
        }
        $page_count = ceil($count / $page_size);
        if ($page_no > $page_count) {
            $page_no = $page_count;
        }
        $query->page($page_no, $page_size);
        $data = $this->findAll($query, $params);
        $ret = array(
                    'total' => $count,
                    'data' => $data,
                    'pageCount' => $page_count,
                    'pageSize' => $page_size,
                    'current' => $page_no,
               );
        return $ret;
    }
    
    /**
     * 使用 sql 语句查询对象
     * 
     * @param string $sql
     * @param array $param
     * @param array $query_array
     * @return ObjectSet
     */
    function bySql($sql, $params = array(), $query_array = null) {
        if (!empty($params)) {
            $sqlBuilder = $this->getSqlBuilder($query_array);
            $sql = $sqlBuilder->parseParams($sql, $params);
        }
        $rs = $this->getDB($query_array)->query($sql);
        $ret = new ObjectSet($rs, $this->className, $this->conf);
        foreach ($this->afterFindCallbacks as $callback) {
            call_user_func($callback, $obj, $param, $ret);
        }
        return $ret;
    }
    
    /**
     * 找到所有符合条件的对象
     * 
     * @param Query $query 查询对象
     * @return ObjectSet
     */
    function findAll($query, $params = array()) {
        foreach ($this->beforeFindCallbacks as $callback) {
            call_user_func($callback, $obj, $param);
        }
        $query->from($this->className);
        $q = $query->getArray();
        $sqlBuilder = $this->getSqlBuilder($q);
        if (empty($q['properties'])) {
            $properties = $this->conf['properties'];
            $q['properties'] = array_keys($properties);
        }
        
        $sql = $sqlBuilder->buildSelect($q);
        return $this->bySql($sql, $params, $q);
    }
    
    /**
     * 得到符合条件的对象数
     * 
     * @param mixed $cond
     * @return int
     */
    function count($cond, $params = array()) {
        $query = new Query();
        $query->from($this->className)
            ->select('count(*)')
            ->where($cond);
        $q = $query->getArray();
        $sqlBuilder = $this->getSqlBuilder($q);
        $sql = $sqlBuilder->buildSelect($q);
        if (!empty($params)) {
            $sql = $sqlBuilder->parseParams($sql, $params);
        }
        $row = $this->getDB($q)->query($sql)->fetch();
        return intval(current($row));
    }
    
    /**
     * 创建一个新对象
     *
     * @return BaseModel
     */
    function createNew() {
        $class = class_exists($this->className) ? $this->className : 'BaseModel';
        $ret = new $class(array(), $this->conf['id'], true);
        self::setNew($ret);
        return $ret;
	}
    
    /**
     * 创建用于更新的数据对象
     *
     * @deprecated
     * @return BaseModel
     */
    function createForUpdate() {
        $class = class_exists($this->className) ? $this->className : 'BaseModel';
		return new $class(array(), $this->conf['id'], false);
	}

    /**
     * 找到符号条件的第一个对象
     * 
     * @param Query $query 查询对象
     * @return BaseModel
     */
    function find($query, $params = array()) {
        $query->limit(1);
        return $this->findAll($query, $params)->fetch();
    }
    
    
    /**
     * 根据主键查找对象
     *
     * @param mixed $id
     * @return BaseModel
     */
    function findById($id, $properties = null) {
        $id_prop = $this->conf['id'];
        return $this->findBy($id_prop, $id, $properties);
    }
    
       
    /**
     * 根据主键查找对象
     *
     * @param array $ids
     * @return BaseModel
     * @deprecated
     */
    function findByIds($ids, $properties = null) {
        return $this->findAllById($ids, $properties);
    }
    
    /**
     * 根据主键查找对象
     *
     * @param array $ids
     * @return BaseModel
     */
    function findAllById($ids, $properties = null) {
        $id_prop = $this->conf['id'];
        return $this->findAllBy($id_prop, $ids, $properties);
    }
    
    /**
     * 根据属性的值查找对象
     *
     * @param string $prop 属性
     * @param mixed $value 属性的值
     * @return ObjectSet
     */
    function findBy($prop, $value, $properties = null) {
        $query = $this->getQuery();
        if (!is_array($prop)) {
            $cond = array($prop => $value);
        } else {
            $cond = array(array($prop, $value));
        }
        $query->where($cond)->select($properties);
        $ret = $this->find($query);
        return $ret;
    }
    
    /**
     * 根据属性的值查找对象
     *
     * @param string $prop 属性
     * @param mixed $value 属性的值
     * @return ObjectSet
     */
    function findAllBy($prop, $value, $properties = null) {
        $query = $this->getQuery();
        if (!is_array($prop)) {
            $cond = array($prop => $value);
        } else {
            $cond = array(array($prop, $value));
        }
        $query->where($cond)->select($properties);
        $ret = $this->findAll($query);
        return $ret;
    }

    
    /**
     * 提供findByXXXX 形式的查找功能
     * 
     */
    function __call($name, $args) {
        foreach (array('findAllBy', 'findBy') as $methodPrefix) {
            if (! StringUtil::beginWith($name, $methodPrefix)) {
                continue;
            }
            $property = substr($name, strlen($methodPrefix));
            $property = StringUtil::lcfirst($property);
            array_unshift($args, $property);
            $ret = call_user_func_array(array($this, $methodPrefix), $args);
            return $ret;
        }
        throw(new Exception("method $name not exists"));
    }
    
    function exists($id) {
        $idProp = $this->conf['id'];
        $count = $this->count(array($idProp=>$id));
        return $count > 0;
    }
    
    /**
     * 新增一个对象
     * 
     * @param BaseModel $obj
     * @param array $param
     * @return int
     */
    function add($obj, $param = array()) {
        foreach ($this->beforeAddCallbacks as $callback) {
            call_user_func($callback, $obj, $param);
        }
        $row = $obj->rawData();
        $query = array(
            'class' => $this->className,
            'data' => $row,
        );
        $ret = $this->execQuery('insert', $query, $param);
        if ($ret) {
            $id = $this->getDB($query)->lastId();
            if ($id > 0) {
                self::setNew($obj, false);
                $obj->saved($id);
            }
        }
        foreach ($this->afterAddCallbacks as $callback) {
            call_user_func($callback, $obj, $param, $ret, $id);
        }
        return $ret;
    }
    
    
    /**
     * 更新一个对象
     *
     * @param array $map
     * @param array $cond
     * @return int
     */
    function update($map, $cond, $param = array()) {
        foreach ($this->beforeUpdateCallbacks as $callback) {
            call_user_func($callback, $map, $cond, $param);
        }
        
        $query = array(
            'class' => $this->className,
            'data' => $map,
            'condition' => $cond
        );
        
        $ret = $this->execQuery('update', $query, $param);
        
        foreach ($this->afterUpdateCallbacks as $callback) {
            call_user_func($callback, $map, $cond, $param, $ret);
        }
        return $ret;
    }
    
    /**
     * 保存一个对象，根据对象状态自动新增或更新
     *
     * @param BaseModel $obj
     * @return int
     */
    function save($obj) {
        foreach ($this->beforeSaveCallbacks as $callback) {
            call_user_func($callback, $obj);
        }
        if (self::isNew($obj)) {
            $ret = $this->add($obj);
        } else {
            $idProp = $this->conf['id'];
            $id = $obj->getId();
            $cond = array_combine((array)$idProp, (array)$id);
            $ret = $this->update($obj->rawData(), $cond);
        }
        foreach ($this->afterSaveCallbacks as $callback) {
            call_user_func($callback, $obj, $ret);
        }
        return $ret;
    }
    
    /**
     * 根据条件删除对象
     *
     * @param array $cond
     * @return int
     */
    function delete($cond, $param = array()) {
        foreach ($this->beforeDeleteCallbacks as $callback) {
            call_user_func($callback, $obj, $param);
        }
        $query = array(
            'class' => $this->className,
            'condition' => $cond
        );
        $ret = $this->execQuery('delete', $query, $param);
        foreach ($this->afterDeleteCallbacks as $callback) {
            call_user_func($callback, $obj, $param, $ret);
        }
        return $ret;
    }
    
    function affected() {
        $ret = $db->affected();
        return $ret;
    }

    /**
     * 根据主键删除对象
     *
     * @param mixed $id
     * @return int
     */
    function deleteById($id) {
        $idProp = $this->conf['id'];
        return $this->delete(array($idProp => $id));
    }
    
    /**
     *
     * @param string $type
     * @param array $query
     */
    protected function execQuery($type, $query, $param = array()) {
        $sqlBuilder = $this->getSqlBuilder($query);
        $func = array($sqlBuilder, 'build' . ucfirst($type));
        $sql = call_user_func($func, $query);
        if (!empty($params)) {
            $sql = $sqlBuilder->parseParams($sql, $params);
        }
        $db = $this->getDB($query);
        $result = $db->execute($sql);
        return $result;
    }
    
    protected function mergeConfig($a, $b) {
        foreach ($b as $k => $v) {
            if (isset($a[$k]) && is_array($a[$k]) && is_array($v)) {
                $a[$k] = $this->mergeConfig($a[$k], $v);
                continue;
            }
            $a[$k] = $v;
        }
        return $a;
    }
    
    static function isNew($obj) {
        $key = spl_object_hash($obj);
        return isset(self::$isNew[$key]) && self::$isNew[$key];
    }
    
    protected static function setNew($obj, $bool = true) {
        $key = spl_object_hash($obj);
        self::$isNew[$key] = $bool;
    }
}