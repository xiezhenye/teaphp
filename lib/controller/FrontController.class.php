<?php

/**
 * 前端控制器
 * @package controller
 */
class FrontController {
    /**
     *
     * @var BaseView
     */
    protected $view;
    
    /**
     * @var App
     */
    protected $app;
    
    
    /**
     *
     * @param App $app
     */
    function __construct($app) {
        ErrorWrapperException::bind();
        $this->app = $app;
    }
    
    function setView($view) {
        $this->view = $view;
    }
    
    static function getActionClass($type, $action_name) {
        return StringUtil::camelize($action_name) . ucfirst($type);
    }
    
    static function getMethodName($http_method, $method) {
        return strtolower($http_method).StringUtil::camelize($method);
    }
    
    function addCallbacks($action, $type, $action_name, $method_name) {
        $before_callbacks = $this->app->conf('app', "actions/$type/hooks/before", array());
        foreach ($before_callbacks as $name => $callback) {
            if (is_string($name)) {
                if (strpos($name, '.') !== false) {
                    $name = explode('.', $name, 2);
                    if ($name[0] != $action_name || $name[1] != $method_name) {
                        continue;
                    }
                } elseif ($name != $action_name) {
                    continue;
                }
            } 
            $action->addBeforeActionCallback($callback);
        }
        $after_callbacks = $this->app->conf('app', "actions/$type/hooks/after", array());
        foreach ($after_callbacks as $name => $callback) {
            if (is_string($name)) {
                if (strpos($name, '.') !== false) {
                    $name = explode('.', $name, 2);
                    if ($name[0] != $action_name || $name[1] != $method_name) {
                        continue;
                    }
                } elseif ($name != $action_name) {
                    continue;
                }
            } 
            $action->addAfterActionCallback($callback);
        }
    }
    
    /**
     * 调用控制器
     *
     * @param string $action_name 模块名
     * @param string $type 动作类型
     * @param string $http_method http方法名
     * @param string $method 方法名
     * @param HTTPRequest $request
     * @param HTTPResponse $response
     */
    function call($action_name, $type, $http_method, $method, $request, $response) {
        $class_name = self::getActionClass($type, $action_name);
        $method_name = self::getMethodName($http_method, $method);
        if (!class_exists($class_name)) {
            $this->view->render(array(
                            'message'=>"no action $class_name",
                            'response_code'=>404
                        ), BaseView::ERROR);
            return;
        }
        if (! method_exists($class_name, $method_name)) {
            $this->view->render(array(
                            'message'=>"no $class_name $method_name",
                            'response_code'=>404
                        ), BaseView::ERROR);
            return;
        }
        $module_name = app()->getClassModule($class_name);
        $request->setParam('_module', $module_name);
        $ret = null;
        try {
            /** @var BaseAction */
            $action = new $class_name();
            // add callbacks
            $this->addCallbacks($action, $type, $action_name, $method_name);
            
            $action->setApp($this->app);
            
            $ret = $action->beforeAction($method_name, $request);
            if (is_null($ret)) {
                if (method_exists($action, $method_name)) {
                    $ret = (array)$action->$method_name($request);
                } else {
                    throw new Exception('no such method');
                }
            }
            //$default_view = $action_name.'_'.$method;
            $default_view = $method;
            if (!array_key_exists(0, $ret)) {
                $ret = array($ret, $default_view);
            } elseif (!isset($ret[1])) {
                $ret[1] = $default_view;
            }
            $action->afterAction($method_name, $request, $ret);

            $this->view->setModule($action->moduleName());
            $this->view->render($ret[0], $ret[1]);
        } catch (ActionException $e) {
            $ret = $e->getActionReturn();
            //$response->sendStatusHeader(500);
            $this->view->render($ret[0], $ret[1]);
        } catch (Exception $e) {
            $response->sendStatusHeader(500);
            $this->view->showError($e);
        }
    }
    
    protected function getViewName($method) {
        StringUtil::underscore($method);
    }
}



class ActionException extends Exception {
    protected $type;
    protected $return_url;
    
    function __construct($message, $type = BaseView::ERROR, $return_url = null) {
        parent::__construct($message, 0);
        $this->type = $type;
        $this->return_url = $return_url;
    }
    
    function getActionReturn() {
        $ret = array(array(
            'message' => $this->message,
            'url' => $this->return_url
        ), $this->type);
        return $ret;
    }
}
