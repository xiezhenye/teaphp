<?php
/**
 * http请求对象
 * @package http
 */
class HTTPRequest {
    
    protected $params;
    
    protected static $instance = null;
    
    protected $flash;
    
    protected $data = array('get'=>array(), 'post'=>array(), 'cookie'=>array(), 'server'=>array());
	
    /**
     *
     * @param array $param 路由请求参数
     */
    function __construct($params = array()) {
        $this->params = $params;
		$this->data['get'] = $_GET;
        $this->data['post'] = $_POST;
        $this->data['cookie'] = $_COOKIE;
		$this->data['server'] = $_SERVER;
		if ($this->method() == 'PUT' && $this->contentMIME() == 'application/x-www-form-urlencoded') {
			$input = file_get_contents('php://input');
			parse_str($input, $this->data['put']);
		}
		if (get_magic_quotes_gpc()) {
            $this->data = array_map(array(__CLASS__, '_stripslashes'), $this->data);
		}
    }
	
    /**
     *
     * @return HTTPRequest
     */
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new HTTPRequest;
        }
        return self::$instance;
    }
    
    public function setVar($type) {
        $args = func_get_args();
        if (count($args) == 2 && is_array($args[1])) {
            $this->data[strtolower($type)] = $args[1];
			return;
        }
        if (count($args) == 3) {
            $key = $args[1];
            $value = $args[2];
            $this->data[strtolower($type)][$key] = $value;
			return;
        }
		throw new Exception('bad argument');
    }
	
	public function contentMIME() {
		$ct = $this->server('CONTENT_TYPE', '');
		list($mime, ) = explode(';', $ct);
		return trim($mime);
	}
	
	public function contentCharset() {
		$ct = $this->server('CONTENT_TYPE', '');
		list($mime, $s) = explode(';', $ct);
		list($name, $value) = explode('=', trim($s));
		if ($name == 'charset') {
			return $value;
		}
		return '';
	}
    
    private static function _stripslashes($var) {
        if (is_array($var)) {
            return array_map(array(__CLASS__, '_stripslashes'), $var);
        }
        return stripcslashes($var);
    }
    
    /**
     * 设置路由请求参数
     * @param array $params
     */
    function setParams($params) {
        $this->params = $params;
    }
    
    /**
     * 设置请求参数
     * @param string $name
     * @param mixed $value
     */
    function setParam($name, $value) {
        $this->params[$name] = $value;
    }
    
	
    /**
     * 客户端IP地址
     *
     * @return string
     */
    function remoteIP($follow = false) {
        if (!$follow) {
            return $this->data['server']['REMOTE_ADDR'];
        }
        if (isset($this->data['server']['HTTP_X_FORWARDED_FOR']) ) {
            $ips = explode (', ', $this->data['server']['HTTP_X_FORWARDED_FOR']);
            if (isset($this->data['server']["HTTP_CLIENT_IP"])) {
                array_unshift($ips, $this->data['server']["HTTP_CLIENT_IP"]);
            }
            foreach ($ips as $ip) {
                $ipa = explode('.', $ip);
                if ($ipa[0] == 0 || $ipa[0] == 10 || $ipa[0] == 127 || $ipa == 172 || $ipa[0] >= 223) {
                    continue;
                }
                if ($ipa[0] == 192 && $ipa[1] == 168) {
                    continue;
                }
                return $ip;
            }
        }
        return isset($this->data['server']['REMOTE_ADDR']) ? $this->data['server']['REMOTE_ADDR'] : '';
    }
    
    /**
     * 请求的uri
     * @return string
     */
    function uri() {
        return isset($this->data['server']['REQUEST_URI']) ? $this->data['server']['REQUEST_URI'] : '';
    }
    
    /**
     * 客户端软件
     * 
     * @return string
     */
    function userAgent() {
        return isset($this->data['server']['HTTP_USER_AGENT']) ? $this->data['server']['HTTP_USER_AGENT'] : '';
    }
    
    /**
     * 请求的来源
     * 
     * @return string
     */

    function referer() {
        return isset($this->data['server']['HTTP_REFERER']) ? $this->data['server']['HTTP_REFERER'] : '';
    }
    
    /**
     * 请求的完整url
     */
    function url() {
        $host = isset($this->data['server']['HTTP_HOST']) ? $this->data['server']['HTTP_HOST'] : '';
        return 'http://'.$host . $this->uri();
    }
    
    /**
     * http get参数
     * @param string $name
     * @param string $default
     * @return mixed
     */
    function get($name = null, $default = null) {
    	if ($name === null) {
    		return $this->data['get'];
    	}
        return isset($this->data['get'][$name]) ? $this->data['get'][$name] : $default;
    }
    
    /**
     * http post参数
     * @param string $name
     * @param string $default
     * @return mixed
     */    
    function post($name = null, $default = null) {
        if ($name === null) {
    		return $this->data['post'];
    	}    	
        return isset($this->data['post'][$name]) ? $this->data['post'][$name] : $default;
    }
	
	
	/**
     * 环境变量以及请求信息
     * 
     * @param string $name
     * @param string $default
     * @return mixed
     */    
	function server($name = null, $default = null) {
		if ($name === null) {
    		return $this->data['server'];
    	}    	
        return isset($this->data['server'][$name]) ? $this->data['server'][$name] : $default;
	}
	
	/**
     * http put参数
     * @param string $name
     * @param string $default
     * @return mixed
     */    
    function put($name = null , $default = null) {
        if ($name === null) {
    		return $this->data['put'];
    	}    	
        return isset($this->data['put'][$name]) ? $this->data['put'][$name] : $default;
    }
     
    /**
     * http 请求参数，包括get, post, cookie
     * @param string $name
     * @param string $default
     * @return mixed
     */   
    function request($name, $default = null) {
        return $this->cookie($name,
					$this->post($name,
						$this->put($name, 
							$this->get($name, $default))));
    }
    
    /**
     * http cookie参数
     * @param string $name
     * @param string $default
     * @return mixed
     */    
    function cookie($name, $default = null) {
        if ($name === null) {
    		return $this->data['cookie'];
    	}
        return isset($this->data['cookie'][$name]) ? $this->data['cookie'][$name] : $default;
    }
    
    /**
     * http 上传文件参数
     * @return UploadedFile
     */    
    function files($name) {
        return empty($_FILES[$name]['name']) ? null : new UploadedFile($_FILES[$name]);
    }
     
    /**
     * 取得一个附加参数
     * @param string $name
     * @param string $default
     * @return mixed
     */   
    function param($name, $default = null) {
        return isset($this->params[$name]) ? $this->params[$name] : $this->request($name, $default);
    }
    
    /**
     * 返回所有的附加参数
     * @return array
     */
    function allParams() {
        return $this->params;
    }
    
    /**
     * 返回 HTTP 请求方法
     * @param bool $restful 是否处理 restful 式样 POST 参数，为true时返回 post 的 REQUEST_METHOD 结果
     * @return string
     */
    function method($restful = false) {
        $ret =  strtoupper($this->data['server']['REQUEST_METHOD']);
        if ($restful && $ret == 'POST') {
			$alias = array('DELETE', 'PUT', 'HEADER', 'GET');
			if (in_array($this->post('REQUEST_METHOD'), $alias)) {
				$ret = $this->post('REQUEST_METHOD');
			} else {
				if (in_array($this->header('Request-Method'), $alias)) {
					$ret = $this->header('Request-Method');
				}
			}
        }
        return $ret;
    }
    
    function flashData() {
        $ret = $this->cookie('_flash');
        $resp = HTTPResponse::getInstance();
        $resp->deleteCookie('_flash','/');
        $ret = json_decode($ret, true);
        return $ret;
    }
	
    /**
     * post 请求的请求体
     * @return string
     */
    function postBody() {
        return file_get_contents('php://input');
    }
    
    function queryString() {
        return isset($this->data['server']['QUERY_STRING']) ? $this->data['server']['QUERY_STRING'] : '';
    }
    
    function accept() {
        return isset($this->data['server']['HTTP_ACCEPT']) ? $this->data['server']['HTTP_ACCEPT'] : '';
    }
    
    /**
     * http 请求头
     *
     * @deprecated
     * @return array
     */
    function headers() {
        return $this->allHeaders();
    }
    
    /**
     * http 请求头
     *
     * @return array
     */
    function allHeaders() {
        $ret = array();
        foreach ($this->data['server'] as $name => $value) {
            if (StringUtil::beginWith($name, 'HTTP_')) {
                $key = substr(str_replace('_', ' ', $name), 5);
                $key = str_replace(' ', '-', ucwords(strtolower($key)));
                $ret[$key] = $value;
            }
        }
        return $ret;
    }
    
    /**
     * http 请求头
     * @return array
     */
    function header($name) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return isset($this->data['server'][$key]) ? $this->data['server'][$key] : null;
    }
        
    /**
     * 转发 http 请求，会携带请求中的主要 header
     *
     * @param string $url 转发目标 url
     * @param bool $return 为 true 时直接输出结果，为 false 时将结果返回
     */
    function forword($url, $return = false) {
        $client = new SimpleHTTPClient();
        
        $headers = $this->headers();
        $forwordHeads = array('User-Agent', 'Accept', 'Accept-Language', 
                              'Accept-Charset', 'Cookie');
        
        foreach ($headers as $k => $v) {
            if (!in_array($k, $forwordHeads)) {
                unset($headers[$k]);
            }
        }
        $body = '';
        if (in_array($this->method(), array('POST', 'PUT'))) {
            $body = $this->postBody();
        }
        return $client->doRequest($this->method(), $url, $headers, $body, $return);
    }
}

/**
 * 包装 $_FILE 的成员
 *
 * 
 */
class UploadedFile {
	private $file;
    
    /**
     * 
     * @param array $fileArray
     */
	function __construct($fileArray) {
		$this->file = $fileArray;
	}
    
    /**
     * 文件大小，单位：byte
     * 
     * @return int 
     */
	function size() {
		return $this->file['size'];
	}
    
    /**
     * 原始文件名
     *
     * @return string
     */
	function name() {
		return $this->file['name'];
	}
    
    /**
     * 客户端提供的 MIME TYPE
     *
     * @return string
     */
	function type() {
		return $this->file['type'];
	}
	
    /**
     * 错误信息
     *
     * @return string
     */
	function error() {
		return $this->file['error'];
	}
    
    /**
     * 临时文件名
     *
     * @return string
     */
	function tmpName() {
		return $this->file['tmp_name'];
	}
    
    /**
     * 将临时文件移动到目标路径
     *
     * @return bool
     */
	function moveTo($path) {
		return move_uploaded_file($this->tmpName(), $path);
	}
}

