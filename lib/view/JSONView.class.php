<?php

class JSONView extends BaseView {
    function render($data, $tpl) {
        switch ($tpl) {
        case BaseView::REDIRECT:
        case BaseView::SUCCESS:
        case BaseView::FAILURE:
        case BaseView::ERROR:
            //HTTPResponse::getInstance()->sendStatusHeader(500);
            $ret = array($tpl => $data);
            break;
        case BaseView::NULL:
            $ret = null;
            break;
        default:
            $ret = $this->toArray($data);
            break;
        }
        echo json_encode($ret);
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
}

