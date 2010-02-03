<?php

/**
 * 前端控制器
 *
 */
class FrontController {
    protected $appPath;
    /**
     *
     * @var BaseView
     */
    protected $view;
    protected $actionConf;
    
    /**
     *
     * @var Dispatcher
     */
    protected $dispatcher;
    
    /**
     * @param string $appPath 应用根目录
     * @param array $appConf 应用配置
     * @param Dispatcher $dispatcher
     */
    function __construct($appPath, $appConf, $dispatcher) {
        ErrorWrapperException::bind();
        $this->dispatcher = $dispatcher;
        $this->appPath = $appPath;
        $this->actionConf = isset($appConf['actions']) ? $appConf['actions'] : array();
        $this->baseUrl = $appConf['url']['base'];
    }
    
    function setView($view) {
        $this->view = $view;
    }
    
    /**
     * 调用控制器
     *
     * @param string $moduleName 模块名
     * @param string $type 动作类型
     * @param string $http_method http方法名
     * @param string $method 方法名
     * @param HTTPRequest $request http 请求对象
     * @param HTTPResponse $response http 响应对象
     */
    function call($moduleName, $type, $http_method, $method, $request, $response) {
        $actionName = StringUtil::camelize($moduleName) . ucfirst($type);
        $methodName = strtolower($http_method).StringUtil::camelize($method);
        if (!class_exists($actionName)) {
            $response->sendStatusHeader(404);
            echo "no action $actionName\n";
            return;
        }
        if (! method_exists($actionName, $methodName)) {
            $response->sendStatusHeader(404);
            echo "no $moduleName $methodName\n";
            return;
        }
        
        $ret = null;
        try {
            $action = new $actionName();
            if (method_exists($action, 'setDispatcher')) {
                $action->setDispatcher($this->dispatcher);
            }
            if (method_exists($action, 'setController')) {
                $action->setController($this);
            }
            if (method_exists($action, 'beforeAction')) {
                $ret = $action->beforeAction($methodName, $request);
            }
            if (is_null($ret)) {
                if (method_exists($action, $methodName)) {
                    $ret = (array)$action->$methodName($request);
                } else {
                    throw new Exception('no such method');
                }
            }
            if (!array_key_exists(0, $ret)) {
                $ret = array($ret, $method);
            } elseif (!isset($ret[1])) {
                $ret[1] = $method;
            }
            if (method_exists($action, 'afterAction')) {
                $action->afterAction($methodName, $request, $ret);
            }
            $this->view->render($ret[0], $ret[1]);
        } catch (ActionException $e) {
            $ret = $e->getActionReturn();
            $this->view->render($ret[0], $ret[1]);
        } catch (Exception $e) {
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
