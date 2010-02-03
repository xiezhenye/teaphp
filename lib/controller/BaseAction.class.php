<?php

class BaseAction {
    /**
     * @var Dispatcher
     */
    protected $dispatcher;
    
    /**
     *
     * @var FrontContrlller
     */
    protected $controller;
    
    private $beforeActionCallbacks = array();
    private $afterActionCallbacks = array();
    
    /**
     *
     * @param Dispatcher $dispatcher
     */
    function setDispatcher($dispatcher) {
        $this->dispatcher = $dispatcher;    
    }
    
    /**
     *
     * @param FrontContrlller $controller
     */
    function setController($controller) {
        $this->controller = $controller;    
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
}
