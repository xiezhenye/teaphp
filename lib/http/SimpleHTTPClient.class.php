<?php
/**
 * 简单 HTTP 客户端
 *
 */
class SimpleHTTPClient {
    protected $response_headers = array();
    /**
     * 发送 http 请求
     *
     * @param string $method 请求方法
     * 
     * @param string $url 目标 url
     * 
     * @param array $headers 请求头
     * 
     * @param string $body 请求体
     * 
     * @param bool $return 为 true 时直接输出结果，为 false 时将结果返回
     */
    function doRequest($method, $url, $headers = array(), $body = '', $return = true) {
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }
        $opts = $this->buildHTTPContextArray($method, $headers);
        if ($opts['http']['method'] == 'POST') {
            $opts['http']['content'] = $body;
        }
        $context = stream_context_create($opts);
        if ($return) {
            $ret = file_get_contents($url, false, $context);
            $this->response_headers = $http_response_header;
            return $ret;
        } else {
            readfile($url, false, $context);
            $this->response_headers = $http_response_header;
        }
    }
    
    function getResponseHeader() {
        return $this->response_headers;
    }
    
    protected function buildHeaderString($head_array) {
        $header_str = '';
        foreach ($head_array as $k => $v) {
            $header_str.= "$k: $v\r\n";
        }
        return $header_str;
    }
    
    protected function buildHTTPContextArray($method, $header_array) {
        $header_str = $this->buildHeaderString($header_array);
        $opts = array(
            'http'=>array(
                'method' => $method,
                'header' => $header_str
            )
        );
        return $opts;
    }
    
    /**
     * 发送 http get 请求
     *
     * @param string $url 目标 url
     * @param bool $return 为 true 时直接输出结果，为 false 时将结果返回
     */
    function get($url, $headers = array(), $return = true) {
        return $this->doRequest('GET', $url, $headers, '', $return);
    }
    
    /**
     * 发送 http post 请求
     *
     * @param string $url 目标 url
     * @param array $postData 请求数据关系数组
     * @param array $headers 请求头
     * @param bool $return 为 true 时直接输出结果，为 false 时将结果返回
     */
    function post($url, $postData, $headers = array(), $return = true) {
        $rawStr = $this->buildRawData($postData);
        return $this->doRequest('POST', $url, $headers, $rawStr, $return);
    }
    
    /**
     * 发送 http put 请求
     *
     * @param string $url 目标 url
     * @param array $postData 请求数据关系数组
     * @param array $headers 请求头
     * @param bool $return 为 true 时直接输出结果，为 false 时将结果返回
     */
    function put($url, $postData, $headers = array(), $return = true) {
        $rawStr = $this->buildRawData($postData);
        return $this->doRequest('PUT', $url, $headers, $rawStr, $return);
    }
    
    /**
     * 发送 http delete 请求
     *
     * @param string $url 目标 url
     * @param array $headers 请求头
     * @param bool $return 为 true 时直接输出结果，为 false 时将结果返回
     */
    function delete($url, $headers = array(), $return = true) {
        return $this->doRequest('DELETE', $url, $headers, '', $return);
    }
    
    function buildRawData($data) {
        $rawData  = array();
        foreach ($postData as $k=>$v) {
            $rawData[] = rawurlencode($k).'='.rawurlencode($v);
        }
        $rawStr = implode('&', $rawData);
        return $rawStr;
    }
    
    
}
