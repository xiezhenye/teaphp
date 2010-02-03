<?php
/**
 * BaseView
 * 
 * 视图基类
 */
abstract class BaseView {
    const LAYOUT = 'layout';
    const NULL = 'null';    
    const REDIRECT = 'redirect';
    const SUCCESS = 'success';
    const FAILURE = 'failure';
    const ERROR = 'error';
    
    
    protected $_appPath;
    /**
     *
     *
     * @var HTTPResponse
     */
    protected $response;
    
    
    /**
     *
     * @var Dispatcher
     */
    public $_dispatcher;
    
    /**
     *
     * @var FrontController
     */
    public $_controller;
    /**
     * 设置应用目录
     *
     * @param string $dir
     */
    function setAppPath($dir) {
        $this->_appPath = $dir;
    }
    
    function setResponse($resp) {
        $this->response = $resp;
    }
    
    /**
     * 设置分派器实例
     * @param Dispatcher $dispatcher
     */
    function setDispatcher($dispatcher) {
        $this->_dispatcher = $dispatcher;
    }
    
    /**
     * 设置前端控制器实例
     * @param FrontController $controller
     */
    function setController($controller) {
        $this->_controller = $controller;
    }
    
    /**
     * 向客户端发送跳转 header
     *
     * @param string $url 跳转的目标 url
     * @param int $code 跳转的状态码，默认为302
     */
    function redirect($url, $code = 302) {
        $this->response->sendStatusHeader($code);
        $this->response->header("Location", $url);
        exit;
    }
    
    /**
     * 渲染模板
     *
     * @param array $_data
     * @param string $_tplName
     */
    abstract function render($_data, $_tplName);
    
    /**
     * @param Exception $exception
     *
     */
    abstract function showError($exception);
}
