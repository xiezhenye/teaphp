<?php
class StringUtil {
    static function isEmail($s) {
        $result = filter_var($s, FILTER_VALIDATE_EMAIL);
        return !empty($result);
    }
    
    static function isURL($s) {
        $result = filter_var($s, FILTER_VALIDATE_URL);
        return !empty($result);
    }
    
    static function beginWith($s, $prefix) {
        return preg_match('/^'.preg_quote($prefix, '/').'/', $s);
    }
    
    static function endWith($s, $surfix) {
        return preg_match('/.*'.preg_quote($surfix, '/').'$/', $s);
    }
   
    static function text2html($s) {
        $ret = htmlspecialchars($s);
        $ret = nl2br(str_replace(' ', '&nbsp;', $ret));
        return $ret;
    }
 
    static function cutString($s, $maxWidth, $trimmarker = '...', $encoding = 'utf-8') {
        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($s, 0, $maxWidth, $trimmarker, $encoding);
        }
        
        $ret = '';
        $width = 0;
        if (function_exists('iconv_substr')) {
            $len = iconv_strlen($s, $encoding);
            $lentm = strlen($trimmarker);
            for ($i = 0; $i < $len; $i++) {
                $char = iconv_substr($s, $i, 1, $encoding);
                $charWidth = strlen($char) > 1 ? 2 : 1;
                if ($width + $charWidth <= $maxWidth) {
                    if ($i == $len - 1)
                        return $ret.$char;
                } elseif ($width + $charWidth + $lentm > $maxWidth) {
                    return $ret.$trimmarker;
                }
                $ret.= iconv_substr($s, $i, 1, $encoding);
                $width+= $charWidth;
            }
            return $ret;
        }
    }
    
    static function camelize($str, $lcfirst = false) {
		$words = explode('_', $str);
		$ret = implode('', array_map('ucfirst', $words));
        if ($lcfirst) {
            $ret = self::lcfirst($ret);
        }
        return $ret;
	}
    
    /**
     * 将以大写分割的字符串转换为以_分割
     *
     * @param string $camelCasedWord
     * @return string
     */
    function underscore($camelCasedWord) {
        $replace = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
        return $replace;
    }
	
	static function singluarize($str) {
		if (substr($str, -3) == 'hes') {
			return substr($str, 0, -2);
		}
		if (substr($str, -3) == 'ies') {
			return substr($str, 0, -3).'y';
		}
		if (substr($str, -1) == 's') {
			return substr($str, 0, -1);
		}
		return $str;
	}
    
    static function lcfirst($str) {
        $str[0] = strtolower($str[0]);
        return $str;
    }
}

