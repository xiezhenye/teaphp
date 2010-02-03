<?php
class HTTPResponse {
    private static $instance = null;
    /**
     * @return HTTPResponse
     */
    static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new HTTPResponse;
        }
        return self::$instance;
    }

    function header($name, $value) {
        header("$name: $value");
    }
    
    function setCookie($name, $value, $param = array(), $update = true) {
        $cookieParam = array(
            'expires' => 0,
            'domain' => '',
            'path' => ''
        );
        $cookieParam = array_merge($cookieParam, $param);
        setcookie($name, $value, $cookieParam['expires'], $cookieParam['path'], $cookieParam['domain']);
        if ($update) {
            $_COOKIE[$name] = $value;
        }
    }
    
    function deleteCookie($name,$path='') {
        $this->setCookie($name, '', array('expires' => time() - 3600 * 24,'path'=>$path));
    }
    
    function contentType($type) {
        $this->header('Content-Type', $type);
    }
    
    function contentEncoding($encoding) {
        $this->header('Content-Encoding', $encoding);
    }
    
    /**
     * 取得 HTTP 状态信息
     *
     * @param int $code http 状态码
     * @return string http 默认状态信息
     */
    function getStatusMessage($code) {
        static $codes = array(
            100 => "Continue",
            101 => "Switching Protocols",
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            203 => "Non-Authoritative Information",
            204 => "No Content",
            205 => "Reset Content",
            206 => "Partial Content",
            300 => "Multiple Choices",
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            304 => "Not Modified",
            305 => "Use Proxy",
            307 => "Temporary Redirect",
            400 => "Bad Request",
            401 => "Unauthorized",
            402 => "Payment Required",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            406 => "Not Acceptable",
            407 => "Proxy Authentication Required",
            408 => "Request Time-out",
            409 => "Conflict",
            410 => "Gone",
            411 => "Length Required",
            412 => "Precondition Failed",
            413 => "Request Entity Too Large",
            414 => "Request-URI Too Large",
            415 => "Unsupported Media Type",
            416 => "Requested range not satisfiable",
            417 => "Expectation Failed",
            500 => "Internal Server Error",
            501 => "Not Implemented",
            502 => "Bad Gateway",
            503 => "Service Unavailable",
            504 => "Gateway Time-out"
        );
        return $codes[intval($code)];
    }
    /**
     * 向客户端发送状态信息
     *
     * @param int $code HTTP 状态码
     * @param string $message http 状态信息，默认为 getStatusMessage 的结果
     */
    function sendStatusHeader($code, $message = '') {
        if (empty($message)) {
            $message = $this->getStatusMessage($code);
        }
        $status = "HTTP/1.1 {$code} {$message}";
        header($status);
        $this->header('Status', $message);
    }
    
    function setFlashData($data) {
        $this->setCookie('_flash', json_encode($data),array("path"=>"/"));
    }
}

