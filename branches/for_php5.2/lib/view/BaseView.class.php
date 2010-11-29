<?php
/**
 * BaseView
 * @package view
 * 视图基类
 */
abstract class BaseView {
    const LAYOUT = '_layout';
    const NULL = '_null';    
    const REDIRECT = '_redirect';
    const SUCCESS = '_success';
    const FAILURE = '_failure';
    const ERROR = '_error';
    
    const NOTFOUND = '_404';
    const FOBIDDEN = '_403';
    
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
    
    protected $_module;
    
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
     *
     * @param string $module_name
     */
    function setModule($module_name) {
        $this->_module = $module_name;
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
