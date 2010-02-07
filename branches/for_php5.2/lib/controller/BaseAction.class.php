<?php

class BaseAction {
    
    /**
     *
     * @var App
     */
    protected $app;
    
    private $beforeActionCallbacks = array();
    private $afterActionCallbacks = array();
    
    /**
     *
     * @param App $app
     */
    function setApp($app) {
        $this->app = $app;
    }
    
    function addBeforeActionCallback($callback) {
        if (!is_callable($callback)) {
            return;
        }
        $this->beforeActionCallbacks[]= $callback;
    }
    
    function addAfterActionCallback($callback) {
        if (!is_callable($callback)) {
            return;
        }
        $this->afterActionCallbacks[]= $callback;
    }
    
    function beforeAction(&$methodName, &$request) {
        foreach ($this->beforeActionCallbacks as $callback) {
            $ret = call_user_func_array($callback, array($methodName, &$request));
            if (!is_null($ret)) {
                return $ret;
            }
        }
    }
    
    function afterAction($methodName, &$request, &$return) {
        foreach ($this->afterActionCallbacks as $callback) {
            $ret = call_user_func_array($callback, array($methodName, &$request, &$return));
            if (!is_null($ret)) {
                return $ret;
            }
        }
    }
    
    /**
     * 用表单数据初始化对象
     *
     * @param mixed $obj
     * @param HTTPRequest $request
     * @param array $properties
     */
    function initByForm($obj, $request, $properties) {
        foreach ($properties as $aProperty) {
            $method = 'set' . ucfirst($aProperty);
            call_user_func(array($obj, $method), $request->post($aProperty));
        }
    }
    
    function moduleName() {
        $class_name = get_class($this);
        $class_path = $this->app->getClassLoader()->pathOf($class_name);
        $arr = explode('/', $class_path);
        return $arr[count($arr) - 3];
    }
    
    function urlFor($type, $action, $method = null, $params = array()) {
        $params['_type'] = $type;
        $params['_action'] = $action;
        $params['_method'] = is_null($method) ? $action : $method;
        $ret = $this->app->getDispatcher()->urlFor($params);
        return $ret;
    }
}
