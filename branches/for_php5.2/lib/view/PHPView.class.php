<?php
/**
 * PHPView
 * use php as template
 * @package view
 */
class PHPView extends BaseView{
    const JSON = '_json';
    
    public $_tplDir = array();
    
    /**
     * 模板文件扩展名，默认为 .tpl.php
     * 
     * @var string
     */
    private $_ext = '.tpl.php';
    public $_data = array();
    public $_curTplName;
    public $_curTplData = '';
    public $_layoutName = BaseView::LAYOUT;
    
    /**
     *
     * @var HTTPRequest
     */
    public $_request;
    
    private $_conf;
    
    /**
     * 
     * 
     */
    function __construct($conf) {
        $this->_conf = $conf;        
    }
    
    function setRequest($request) {
        $this->_request = $request;
    }
    /**
     *
     * @return HTTPRequest
     */
    function getRequest() {
        return $this->_request;
    }
    
    private function init() {
        $param = $this->_request->allParams();
        
        $this->_tplDir['module'] = $this->_appPath . "/modules/".$this->_module .
                                "/templates/" . $param['_type'];
        $this->_tplDir['app'] = $this->_appPath . '/templates/' . $param['_type'];
        $this->_tplDir['extern'] = isset($this->_conf['extern']) ? $this->_conf['extern'] : '';
        if (!isset($this->_conf['status'])) {
            $this->_conf['status'] = 'app';
        }
        $this->_tplDir['status'] = $this->_tplDir[$this->_conf['status']];
        
        $this->_tplDir['layout'] = isset($this->_conf['layout']) ?
                        $this->_tplDir[$this->_conf['layout']] :
                        '';
    }
        
    /**
     * 渲染模板
     *
     * @param array $_data
     * @param string $_tplName
     */
    function render($_data, $_tplName) {
        $this->init();
        v::pushView($this);
        
        $this->_data = $_data;
        if (isset($this->_conf['data'])) {
            $this->_data = array_merge($this->_data, (array)$this->_conf['data']);
        }
        $_path = $this->tplPath($this->_tplDir['module'], $_tplName);
        if (!is_file($_path)) {
            $this->renderDefaultView($_data, $_tplName);
            v::popView();
            return;
        }
        
        unset($_data);
        extract($this->_data);
        ob_start();
        $_old_error_reporting = error_reporting();
        error_reporting($_old_error_reporting & ~E_NOTICE);
        try {
            include $_path;
        } catch (Exception $e) {
            if (defined('DEBUG_MODE')) {
                echo $e->getMessage();
            }
        }
        error_reporting($_old_error_reporting);
        $this->_curTplData = ob_get_clean();
        if (!empty($this->_tplDir['layout'])) { //use layout
            $_path = $this->tplPath($this->_tplDir['layout'], $this->_layoutName);
            include $_path;
        } else {
            echo $this->_curTplData;
            
        }
        v::popView();
    }
    
    protected function tplPath($dir, $name) {
        return $dir . DIRECTORY_SEPARATOR . $name . $this->_ext;
    }
    
    function renderDefaultView($_data, $_tplName) {
    	
    	if ($_tplName == BaseView::NOTFOUND) {
    		HTTPResponse::getInstance()->sendStatusHeader(404);
    	}
        $path = $this->tplPath($this->_tplDir['extern'], $_tplName);
        if (is_file($path)) {
            if (isset($this->_conf['data'])) {
                $_data = array_merge($_data, (array)$this->_conf['data']);
            }
            extract($_data);
            unset($_data);
            include $path;
            return;
        }
        
        switch ($_tplName) {
        case BaseView::REDIRECT:
        case BaseView::SUCCESS:
        case BaseView::FAILURE:
        case BaseView::ERROR:
        case BaseView::NOTFOUND:
            // BaseView::SUCCESS 视图默认行为，跳转，如未设置 url 则到来源
            
            if (isset($_data['url'])) {
                $url = $_data['url'];
            } else {
                $ref = $this->_request->referer();
                $url = $ref ? $ref : strval($_data);
            }
            $code = isset($_data['response_code']) ? $_data['response_code'] : 302;
            $resp = HTTPResponse::getInstance();
            $resp->setFlashData(array(
                                'type' => $_tplName,
                                'message' => isset($_data['message']) ? $_data['message'] : '',
                                
                                ));
            $this->redirect($url, $code);
            break;
        case PHPView::JSON:
            $json_view = new JSONView();
            $json_view->render($_data, null);
            break;
        case BaseView::NULL:
            // BaseView::NULL 视图默认行为，直接退出
            //exit;
            break;
        default:
            throw new Exception("缺少模板 $_tplName");
            break;
        }
    }
        
    /**
     *
     * @param Exception $exception
     */
    function showError($exception) {
        $this->init();
        if (isset($this->_conf['data'])) {
            extract((array)$this->_conf['data']);
        }

        v::pushView($this);
        $path = $this->_tplDir['status'].'/error.tpl.php';
        if (is_file($path)) {
            include $path;
        } else {
            //echo '<html><head>Error!</title></head><body>';
            echo '<p>';
            $msg = $exception->getMessage().' : ['.$exception->getCode()."]\n".
                    ' on '.$exception->getFile().' ('.$exception->getLine().")\n".
                    $exception->getTraceAsString()."\n";
            v::text($msg);
            echo '</p>';
            //echo '</body></html>';
        }
        v::popView();
    }
    
    /**
     * 调用模块的 Block 动作
     *
     * @param string $module Action名
     * @param string $method 方法名
     * @param array $params request参数
     */
    function useBlock($action, $method, $params = array(), $return = false) {
        $params['_action'] = $action;
        $params['_method'] = $method;
        $params['_type'] = 'block';
        $params['_view'] = 'PHPView';
        $request = new HTTPRequest($params);
        $old = $_SERVER['REQUEST_METHOD'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        //$this->_controller->call($module, $type, 'get', $method, $request, HTTPResponse::getInstance());
        $ret = '';
        try {
            if ($return) {
                ob_start();
                $this->_dispatcher->doDispatch($params, $request);
                $ret = ob_get_clean();
            } else {
                $this->_dispatcher->doDispatch($params, $request);
            }
        } catch (Exception $e) {
            if ($return) {
                ob_end_clean();
            }
            // ignore error
        }
        $_SERVER['REQUEST_METHOD'] = $old;
        return $ret;
    }
}

/**
 * View Helpers
 * 
 */
class v {
    
    /**
     * @var PHPView PHPView instance
     */
    private static $tpl = array();
    
    private static $data = array();
    
    /**
     * 设置视图的数据
     *
     * @param string $name
     * @param mixed $value
     */
    static function set($name, $value) {
        v::$data[$name] = $value;
    }
    
    /**
     * 读取视图的数据
     *
     * @param string $name
     */
    static function get($name, $default = null) {
        return isset(v::$data[$name]) ? v::$data[$name] : $default;
    }
    
    /**
     * 设置模板实例
     * 
     * @param PHPView $tpl
     */    
    static function pushView($tpl) {
        array_push(self::$tpl, $tpl);
    }

    static function popView() {
        array_pop(self::$tpl);
    }
    
    /**
     * 返回模板实例
     * 
     */    
    static function getView() {
        return end(self::$tpl);
    }
    
    /**
     * 选择指定的 layout 模板
     *
     * @param string $name
     */
    static function setLayout($name) {
        end(self::$tpl)->_layoutName = $name;
    }
    
    /**
     * 输出html转码过的字符串
     * 
     * @param string $s
     */    
    static function out($s) {
        echo htmlspecialchars((string)$s, ENT_QUOTES);
    }
    
    /**
     * 输出html转码，并转换换行和空格的字符串
     * 
     * @param string $s
     */    
    static function text($s) {
        $ret = htmlspecialchars((string)$s, ENT_QUOTES);
        $ret = nl2br(str_replace(' ', '&nbsp;', $ret));
        $ret = preg_replace('/&nbsp;(?!&nbsp;)/', ' ', $ret);
        echo $ret;
    }
    
    /**
     * 输出格式化日期
     * 
     * @param string $format
     * @param mixed $date
     */
    static function date($format, $date) {
        if (is_string($date)) {
            $date = strtotime($date);
        }
        echo date($format, $date);
    }
    
    /**
     * 输出url转码过的字符串
     * 
     * @param string $s
     */
    static function url($s) {
        echo urlencode($s);
    }
    
    /**
     * 载入模块模板
     *
     * @param string $tplName 模板名
     */
    static function moduleTpl($tplName, $extraData = array()) {
        self::loadTpl(end(self::$tpl)->_tplDir['module'], $tplName, $extraData);
    }
    
    /**
     * 载入外部模板
     *
     * @param string $tplName 模板名
     */
    static function externTpl($tplName, $extraData = array()) {
        self::loadTpl(end(self::$tpl)->_tplDir['extern'], $tplName, $extraData);
    }
    
    /**
     * 载入应用模板
     *
     * @param string $tplName 模板名
     */
    static function appTpl($tplName, $extraData = array()) {
        self::loadTpl(end(self::$tpl)->_tplDir['app'], $tplName, $extraData);
    }
    
    static function loadTpl($dir, $tplName, $extraData) {
        extract(end(self::$tpl)->_data);
        extract($extraData);
        try {
            include $dir . '/'. $tplName . '.tpl.php';
        } catch (Exception $e) {
            //
        }
    }
    
    /**
     * 在 layout 模板中载入主模板
     * 
     */
    static function loadMain() {
        echo end(self::$tpl)->_curTplData;
        //extract(self::$tpl->_data);
        //include self::$tpl->_tplDir['module'] . '/'. self::$tpl->_curTplName . '.tpl.php';
    }
    
    /**
     * 输出远程 url 地址的内容
     * 
     * @param string $url
     */
    static function loadRemote($url, $forword = false) {
        if ($forword) {
            $req = HTTPRequest::getInstance();
            $req->forword($url);
        } else {
            readfile($url);
        }
    }
    
    /**
     * 禁用 Layout
     *
     */
    static function noLayout() {
        end(self::$tpl)->_tplDir['layout'] = '';
    }
    
    /**
     * 指定的 layout 模板目录
     *
     * @param string $name
     */    
    static function setLayoutDir($dir) {
        end(self::$tpl)->_tplDir['layout'] = $dir;
    }
    
    /**
     * 调用 block 模块
     *
     * @param string $module 模块名
     * @param string $method 方法名
     * @param array $param 额外参数
     */
    static function useBlock($module, $method, $params = array()) {
        end(self::$tpl)->useBlock($module, $method, $params);
    }
    
    /**
     * 输出一个 http 请求方法的隐藏 input
     * 
     * @param string $method 方法名，以为 'DELETE', 'PUT', 'HEADER', 'GET'
     */
    static function httpMethodInput($method) {
        $method = strtoupper($method);
        if (in_array($method, array('DELETE', 'PUT', 'HEADER', 'GET'))) {
            v::hiddenField('REQUEST_METHOD', $method);
        }
    }
    
    /**
     * 输出一个隐藏域
     *
     * @param string $name
     * @param string $value
     */
    static function hiddenField($name, $value) {
        echo '<input name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($value).'" type="hidden" />';
    }
    
    /**
     * 输出资源的 url 地址
     *
     * @param array $params
     */
    static function urlFor($type, $action, $method = null, $params = array()) {
        $params['_type'] = $type;
        $params['_action'] = $action;
        $params['_method'] = is_null($method) ? $action : $method;
        $url = end(self::$tpl)->_dispatcher->urlFor($params);
        echo $url;
    }
    
    /**
     * 输出映射表对应的内容
     *
     * @param array $map
     * @param string $key
     * @param string $default
     */
    static function map($map, $key, $default = '') {
        echo isset($map[$key]) ? $map[$key] : $default;
    }
    
    /**
     * 输出一个分页控件
     *
     * @param int $current 当前页
     * @param int $total 总记录数
     * @param int $pageSize 每页记录数
     * @param string $urlPattern url模式，printf格式
     */
    static function pager($current, $total, $pageSize, $urlPattern, $placer = '%d') {
        $pager = new Pager($placer);
        $pattern = $urlPattern;
        $pager->draw($current, $total, $pageSize, $pattern);
    }

    static function autoPager($current, $total, $pageSize, $param = 'page') {
        $req = HTTPRequest::getInstance();
        $http_get = $req->get(); 
        $placer = '__tea_php_pager__';
        $http_get[$param] = $placer; 
        $path = $req->param('path');
        v::pager($current, $total, $pageSize, $path . "?" . http_build_query($http_get), $placer);
    }   
 
    /**
     * 输出一个动作表单
     *
     * @param string $method http请求方法
     * @param string $url 请求url，当为数组时，下标0为url模式，其他为值
     * @see v::urlFor
     * 
     * @param string $name 表单的name
     * @param array $params 表单域1
     */
    static function actionForm($method, $url, $name, $params = array()) {
        echo '<form method="post" action="';
        if (is_array($url)) {
            call_user_func_array(array('v', 'urlFor'), $url);
        } else {
            v::urlFor($url);
        }
        echo '" name="' . $name . '" style="display:none;">';
        echo '<input name="REQUEST_METHOD" value="'.strtoupper($method).'" type="hidden" />';
        foreach ($params as $k => $v) {
            if (is_int($k)) {
                echo '<input type="hidden" name="'.$v.'"/>';
            } else {
                echo '<input type="hidden" name="'.$k.'" value="'.htmlspecialchars($v, ENT_QUOTES).' "/>';
            }
        }
        echo '</form>';
    }
    
    /**
     * 输出一个动作链接
     *
     * @param string $text 链接的文字
     * @param string $form 表单的name
     * @param array $params 表单field的值
     * @param string $confirm 确认框文字
     * @param array $attr 额外的标签属性
     */
    static function actionLink($text, $form, $params, $confirm = '', $attr = array(), $callback='') {
        echo '<a ';
        foreach ($attr as $k => $v) {
            echo htmlspecialchars($k, ENT_QUOTES).'="'.htmlspecialchars($v, ENT_QUOTES).'"';
        }
        echo ' href="#" onclick=\'';
        if (!empty($confirm)) {
            echo 'if(!confirm('.htmlspecialchars(json_encode($confirm),ENT_QUOTES).')){ return false; }';
        }
        foreach ($params as $k=>$v) {
            echo 'document.'.$form.'.'.$k.'.value='.htmlspecialchars(json_encode($v),ENT_QUOTES).';';
        }
        if (!empty($callback)) {
        	echo $callback;
        }else{
        	echo 'document.'.$form.'.submit(); return false;';
        }
        echo '\'>'.$text.'</a>';
    }
    
    /**
     * 输出一个动作按钮
     *
     * @param string $text button的value
     * @param string $form 表单的name
     * @param array $params 表单field的值
     * @param string $confirm 确认框文字
     */
    static function actionButton($text, $form, $params, $confirm = '') {
        echo '<input type="button" onclick=\'';
        if (!empty($confirm)) {
            echo 'if(!confirm('.json_encode($confirm).')){return false;}';
        }
        foreach ($params as $k=>$v) {
            echo 'document.'.$form.'.'.$k.'.value='.json_encode($v).';';
        }
        echo 'document.'.$form.'.submit();\' value="'.htmlspecialchars($text, ENT_QUOTES).'" />';
    }
    
    static function selectOptions($data, $default = null) {
        $option = '';
        foreach ($data as $key => $val) {
            $selected = '';
            if ($key == $default) {
                $selected = ' selected="selected"';
            }
            $option .= "<option value=\"$key\"$selected>$val</option>\n";
        }
        return $option;
    }
    
    static function select($name, $data, $default = null) {
        echo '<select name="'.htmlspecialchars($name).'">';
        echo self::selectOptions($data, $default);
        echo '</select>';
    }
    
    static function radioBoxGroup($name, $data, $default = null) {
        $out = '';
        foreach ($data as $value => $label) {
            $checked = '';
            if ($value == $default) {
                $checked = 'checked="checked" ';
            }
            $out.= '<label><input type="radio" '.$checked.
                    'name="'.htmlspecialchars($name).
                    '" value="'.htmlspecialchars($value).'">'.
                    htmlspecialchars($label).'</label>';
        }
        echo "<span class='radioBoxGroup'>$out</span>";
    }
    
    /**
     * 返回一次性数据
     *
     */
    static function flashData($name = null) {
        static $data = null;
        if (is_null($data)) {
            $req = HTTPRequest::getInstance();
            $data = $req->flashData();
        }
        if (!is_null($name)) {
            return $data[$name];
        }
        return $data;
    }

    /**
     * 输出 flashMessage
     *
     */
    static function flashMessage() {        
        if (v::flashData()) {
            echo '<div id="flashMessage" class="', v::flashData('type'), '">';
            v::out(v::flashData('message'));
            echo '</div>';
        }
    }
    
    static function getRequest() {
        return v::getView()->getRequest();
    }
}

