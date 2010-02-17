<?php

class HTTPRequest {
    
    protected $params;
    
    protected static $instance = null;
    
    protected $flash;
    
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

    /**
     * 自动判断magic_quote设置，去掉添加的反斜杠
     *
     */
    static function autoStripslashes() {
        static $stripped = false;
        if (!$stripped && get_magic_quotes_gpc()) {
            $_GET = self::_stripslashes($_GET);
            $_POST = self::_stripslashes($_POST);
            $_COOKIE = self::_stripslashes($_COOKIE);
            $_REQUEST = self::_stripslashes($_REQUEST);
            $stripped = true;
        }
    }
    
    private static function _stripslashes($var) {
        if (is_array($var)) {
            return array_map(array(__CLASS__, '_stripslashes'), $var);
        }
        return stripcslashes($var);
    }
    
    /**
     *
     * @param array $params 路由请求参数
     */
    function __construct($params = array()) {
        $this->params = $params;
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
            return $_SERVER['REMOTE_ADDR'];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
            $ips = explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                array_unshift($ips, $_SERVER["HTTP_CLIENT_IP"]);
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
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }
    
    /**
     * 请求的路径
     * @return string
     */
    function path() {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }
    
    /**
     * 客户端软件
     * 
     * @return string
     */
    function userAgent() {
        return $this->header('USER-AGENT');
    }
    
    /**
     * 请求的来源
     * 
     * @return string
     */
    function referer() {
        return $this->header('referer');
    }
    
    function host() {
        return $this->header('host');
    }
    
    /**
     * 请求的完整url
     */
    function url() {
        $host = $this->host();
        $ret = $this->path();
        if ($host != '') {
            $ret = 'http://'.$host . $ret;
        }
        return $ret;
    }
    
    /**
     * http get参数
     * @param string $name
     * @param string $default
     * @return mixed
     */
    function get($name = null, $default = null) {
    	if ($name === null) {
    		return $_GET;
    	}
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }
    
    /**
     * http post参数
     * @param string $name
     * @param string $default
     * @return mixed
     */    
    function post($name = null , $default = null) {
        if ($name === null) {
    		return $_POST;
    	}    	
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }
     
    /**
     * http 请求参数，包括get, post, cookie
     * @param string $name
     * @param string $default
     * @return mixed
     */   
    function request($name, $default = null) {
        return $this->cookie($name, $this->post($name, $this->get($name, $default)));
    }
    
    /**
     * http cookie参数
     * @param string $name
     * @param string $default
     * @return mixed
     */    
    function cookie($name, $default = null) {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
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
        $ret =  strtoupper($_SERVER['REQUEST_METHOD']);
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
        return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
    }
    
    function accept() {
        return $this->header('accept');
    }
    
    /**
     * http 请求头
     *
     * @return array
     */
    function allHeaders() {
        $ret = array();
        foreach ($_SERVER as $name => $value) {
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
     * 
     * @param string $name
     * @param string $default
     * @return array
     */
    function header($name, $default = null) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }
    
    function getSession() {
        $sess = Session::getInstance();
        return $sess;
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
     * 原始文件的扩展名
     *
     * @return string
     */
    function extName() {
        $ret = strtolower(substr(strrchr($this->name(), '.'), 1));
        return $ret;
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


class Session implements ArrayAccess {
    /**
     * @param string $handler
     * @param array $conf
     * 
     * @return Session
     */
    static function getInstance($handler = 'file', $conf = array()) {
        static $ret = null;
        if (is_null($ret)) {
            $ret = new Session($handler, $conf);
        }
        return $ret;
    }
    
    /**
     *
     * @param string $handler
     * @param array $conf
     */
    private function __construct($handler, $conf) {
        if (isset($conf['domain'])) {
            ini_set('session.cookie_domain', $conf['domain']);
        }
        if ($handler == 'memcache') {
            $this->initMemcacheHandler($conf);
        }
        session_start();
    }
    
    private function initMemcacheHandler($conf) {
        $host = $conf['host'];
        ini_set('session.save_handler', 'memcache');
        $url = "tcp://$host";
        //$url.= "?persistent=0&weight=1&timeout=1&retry_interval=5";
        ini_set('session.save_path', $url);
    }
    
    function OffsetGet($offset) {
        return $this->OffsetExists($offset) ? $_SESSION[$offset] : null;
    }
    
    function OffsetExists($offset) {
        return isset($_SESSION[$offset]);
    }
    
    function OffsetUnset($offset) {
        unset($_SESSION[$offset]);
    }
    
    function OffsetSet($offset, $value) {
        $_SESSION[$offset] = $value;
    }
    
    function set($key, $value) {
        return $this->OffsetSet($key, $value);
    }
    
    function get($key) {
        return $this->OffsetGet($key);
    }
    
    function remove($key) {
        return $this->OffsetUnset($key);
    }
    
    function has($key) {
        return $this->OffsetExists($key);
    }
}
