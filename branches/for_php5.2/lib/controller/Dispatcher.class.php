<?php
/**
 * 分派器
 *
 */
class Dispatcher {
    /**
     * @var string
     */
    protected $appPath;
    /**
     * @var array
     */
    protected $conf;
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
        $this->appPath = $app->path();
        $this->conf = $app->conf('app');
	$this->app = $app;
    }
    
    /**
     * 根据 uri 来调用控制器方法
     *
     * @param HTTPRequest $request
     */
    function dispatch($url, $request) {
        $params = $this->parse($url, $request, $this->conf);
        if (empty($params)) {
            HTTPResponse::getInstance()->sendStatusHeader(404);
            return;
        }
        $this->doDispatch($params, $request);
    }
   
    function doDispatch($params, $request) {
        $type = $params['type'];
        $viewName = $params['view'];
        $conf = isset($this->conf['actions'][$type]['view'][$viewName])
            ? $this->conf['actions'][$type]['view'][$viewName]
            : array();
        $response = HTTPResponse::getInstance(); 
        $view = new $viewName($conf);
        $view->setDispatcher($this);
        $view->setAppPath($this->appPath);
	if (method_exists($view, 'setRequest')) {
	    $view->setRequest($request);
	}
        $view->setResponse($response);
        
        if (is_null($params)) {
            $response->sendStatusHeader(404);
            return;
        }
        
        if (!isset($params['method'])) {
            $params['method'] = $params['module'];
        }
        
        $request->setParams($params);
        
        $controller = new FrontController($this->app);
        $controller->setView($view);
        $view->setController($controller);
        $controller->call($params['module'],
                          $type,
                          $request->method(true),
                          $params['method'],
                          $request,
                          $response);

    }
 
    /**
     * 输出资源的 url 地址
     *
     */
    function urlFor($module, $action, $method, $type) {
        
    }
    
    /**
     * 解析uri的参数
     * 
     * @param string $url
     * @param HTTPRequest $request
     * @param array $conf
     */
    function parse($url, $request, $conf) {
	$parsed = parse_url($url);
        foreach ($conf['route'] as $regexp => $map) {
	    $path = $parsed['path'];
	    if (StringUtil::beginWith($path, $this->getBaseUrl())) {
		$path = substr($path, strlen($this->getBaseUrl()));
	    }
            $matched = preg_match("($regexp)i", $path, $m);
            if (! $matched) { //没有匹配规则转到下一条 
                continue;
            }
            unset($m[0]);
            foreach ($map as $k => $v) {
                if (is_int($k)) {
                    $ret[$v] = rawurldecode($m[$k + 1]);
                    unset($m[$k + 1]);
                } else {
                    $ret[$k] = $v;
                }
            }
            
            $p = array();
            foreach ($m as $v) {
                $p[]= urldecode($v);
            }
            
            $ret['path'] = $parsed['path'];
            $ret['query'] = array();
            isset($parsed['query']) ? parse_str($parsed['query'], $ret['query']) : array();
            $_GET = $ret['query'];
            HTTPRequest::autoStripslashes();
            //$ret['path_seperated'] = array_slice(explode('/', $ret['path']), 1);
            $ret['params'] = $p;
	    if (!isset($ret['view'])) {
		$accepts = array_map('trim', explode(',', $request->accept()));
		if (in_array('text/json', $accepts)) {
			$ret['view'] = 'JSONView';
		} else {
			$ret['view'] = 'PHPView';
		}
	    }
            return $ret;
        }
        return null;
    }
    
    function getBaseUrl() {
        return $this->conf['url']['base'];
    }
}




