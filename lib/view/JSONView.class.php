<?php
/**
 * json视图
 * @package view
 */
class JSONView extends BaseView {
    const JSONP = '_jsonp';
    
    function transform($data, $tpl) {
        switch ($tpl) {
        case BaseView::SUCCESS:
        case BaseView::FAILURE:
        case BaseView::ERROR:
            $ret = array($tpl => $data);
            break;
        case BaseView::NULL:
            $ret = null;
            break;
        case BaseView::REDIRECT:
            $url = $data['url'];
            $this->redirect($url);
            break;
        default:
            $ret = $this->toArray($data);
            break;
        }
        return $ret;
    }
    
    function render($data, $tpl) {
        $ret = $this->transform($data, $tpl);
        if ($tpl == self::JSONP) {
            if (isset($ret['jsonp_callback'])) {
                $callback = $ret['jsonp_callback'];
                unset($ret['jsonp_callback']);
            } else {
                $callback = 'jsonp_callback';
            }
            
            $json = json_encode($ret);
            $json = "$callback($json);";
        } else {
            $json = json_encode($ret);
        }
        $resp = HTTPResponse::getInstance();
        $resp->contentType('application/json');
        echo $json;
    }
    
    function toArray($data) {
        if (is_array($data) || $data instanceof Iterator) {
            $ret = array();
            foreach ($data as $k=>$v) {
                $ret[$k] = $this->toArray($v);
            }
        } elseif ($data instanceof Record) {
            $ret = $data->rawData();
            foreach ($data->allAttaches() as $prop => $a) {
                $ret[$prop] = $this->toArray($a);
            }
        } elseif ($data instanceof NullObject) {
            return null;
        } else {
            $ret = $data;
        }
        return $ret;
    }
    
    /**
     * @param Exception $exception
     */
    function showError($exception) {
        $ret = array(
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        );
        echo json_encode(array('err'=>$ret));
    }
    
    /**
     *
     * @param array $ret
     * @param HTTPRequest $req
     */
    static function autoJSONP($ret, $req, $param_name = 'jsonp') {
        $callback = $req->get($param_name);
        if (empty($callback)) {
            return $ret;
        }
        $ret['jsonp_callback'] = $callback;
        if (isset($ret[0]) && isset($ret[1])) { // 已返回视图
            $ret[1] = self::JSONP;
        } else {
            $ret = array($ret, self::JSONP);
        }
        return $ret;
    }
}

