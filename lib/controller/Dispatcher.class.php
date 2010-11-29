<?php
/**
 * 分派器
 * @package controller
 */
class Dispatcher {
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
        $this->conf = $app->conf('app');
	$this->app = $app;
    }
    
    /**
     * 根据 uri 来调用控制器方法
     *
     * @param HTTPRequest $request
     */
    function dispatch($url, $request) {
        $params = $this->parse($url, $request, $this->app->conf('route'));
            $uri = '/';
        }
        //HTTPRequest::autoStripslashes();
        $request = HTTPRequest::getInstance();
        $params = $this->parse($uri, $request, $this->conf);
        if (empty($params)) {
            HTTPResponse::getInstance()->sendStatusHeader(404);
            return;
        }
        $this->doDispatch($params, $request);
    }
   
    function doDispatch($params, $request) {
        $type = $params['_type'];
        $viewName = $params['_view'];
        $conf = $this->app->conf('app', "actions/$type/view/$viewName", array());
        $response = HTTPResponse::getInstance();
	/** @var BaseView */
        $view = new $viewName($conf);
        $view->setDispatcher($this);
        $view->setAppPath($this->app->path());
	
	
		if (method_exists($view, 'setRequest')) {
			$view->setRequest($request);
		}
        $view->setResponse($response);
        
        if (is_null($params)) {
            $response->sendStatusHeader(404);
            return;
        }
        
        if (!isset($params['_method'])) {
            $params['_method'] = $params['_action'];
        }
        
        $request->setParams($params);
        
        $controller = new FrontController($this->app);
        $controller->setView($view);
        $view->setController($controller);
        $controller->call($params['_action'],
                          $type,
                          $request->method(true),
                          $params['_method'],
                          $request,
                          $response);

    }
 
    /**
     * 输出资源的 url 地址
     *
     * @param string $uri 可以使用 printf 样式占位符
     * @param mixed ... 参数
     */
    function urlFor($params, $query = array(), $with_base_url = true) {
	if (!isset($params['_method'])) {
	    $params['_method'] = $params['_action'];
	}
        $conf = $this->app->conf('route');
	foreach ($conf as $conf_key => $conf_item) {
	    $map = array();
	    $ci = $conf_item;
	    foreach ($params as $param_name=>$param_value) {
		if (isset($ci['params'][$param_name])) {
		    if ($ci['params'][$param_name] != $param_value) {
		        continue 2;
		    }
		    unset($ci['params'][$param_name]);
		    continue 1;
		}
		if (!isset($ci['patterns'][$param_name])) {
		    //默认方法名
		    if ($param_name == '_method' &&
			rawurlencode($param_value) == $map['{_action}']) {
			continue 1;
		    }
		    continue 2;
		}
		$sub_pattern = '(^'.$conf_item['patterns'][$param_name].'$)i';
		if (!preg_match($sub_pattern, $param_value)) {
		    continue 2;
		}
		unset($ci['patterns'][$param_name]);
		$map['{'.$param_name.'}'] = rawurlencode($param_value);
	    }
	    if (empty($ci['patterns']) && empty($ci['params'])) {
		$ret = strtr($conf_key, $map);
		if ($with_base_url) {
		    $ret = $this->app->conf('app', 'base_url').$ret;
		}
		$qstr = http_build_query($query);
		if ($qstr != '') {
		    $ret.= '?'.$qstr;
		}
        return $ret;
    }
	}
	return '';
    }
    
    function buildRegex($conf_key, $conf_item) {
	if (!isset($conf_item['patterns']['_action'])) {
	    $conf_item['patterns']['_action'] = '\w+';
	}
	if (!isset($conf_item['patterns']['_method'])) {
	    $conf_item['patterns']['_method'] = '\w+';
	}
	$map = array();
	foreach ($conf_item['patterns'] as $k=>$v) {
	    //if (!preg_match('/^\:?\w+$/S', $k)) {
	    //	throw new Exception('bad param name');
	    //}
	    $map['{'.$k.'}'] = '(?P<'.$k.'>'.$v.')';
	}
	$conf_key = strtr(preg_quote($conf_key), array('\{'=>'{', '\}'=>'}'));
	
	$regex = '(^'.strtr($conf_key, $map).'$)i';
	return $regex;
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
	$path = $parsed['path'];
	if (StringUtil::beginWith($path, $this->getBaseUrl())) {
	    $path = substr($path, strlen($this->getBaseUrl()));
	}
        foreach ($conf as $conf_key => $conf_item) {
	    $regex = $this->buildRegex($conf_key, $conf_item);
            $matched = preg_match($regex, $path, $m);
            if (!$matched) { //没有匹配规则转到下一条 
                continue;
            }
            unset($m[0]);
	    foreach ($conf_item['params'] as $k => $v) {
		$m[$k] = $v;
	    }
	    $ret = array_map('rawurldecode', $m);
            $ret['_path'] = $parsed['path'];
            $ret['_query'] = array();
            if (isset($parsed['query'])) {
		parse_str($parsed['query'], $ret['_query']);
	    }
            $_GET = $ret['_query'];
            HTTPRequest::autoStripslashes();
            //$ret['_path_seperated'] = array_slice(explode('/', $ret['path']), 1);
	    if (!isset($ret['_view'])) {
			$request->setVar('get', $ret['query']);
            $ret['path_seperated'] = array_slice(explode('/', $ret['path']), 1);
            $ret['params'] = $p;
		$accepts = array_map('trim', explode(',', $request->accept()));
		if (in_array('text/json', $accepts)) {
		    $ret['_view'] = 'JSONView';
		} else {
		    $ret['_view'] = 'PHPView';
		}
	    }
            return $ret;
        }
        return null;
    }
    
    
    function getBaseUrl() {
        return $this->conf['base_url'];
    }
}




