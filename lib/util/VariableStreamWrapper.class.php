<?php
/**
* Variable Stream Wraper
*
* @author xiezhenye <xiezhenye@gmail.com>
* @since 2007-4-9
*/
class VariableStreamWrapper {
    protected $position;
    protected static $var = array();
    protected $varName = '';
    protected $aTime;
    protected $mTime;
    protected $cTime;
    
    protected function get_key($path) {
        if (!preg_match('~^\w+://(.+)$~', $path, $m)) {
            return false;
        }
        return $m[1];
    }
    
    function stream_open($path, $mode, $options, &$opened_path) {
        $this->varName = $this->get_key($path);
        if (!isset(self::$var[$this->varName])) {
            self::$var[$this->varName] = '';
            $this->cTime = time();
        }
        $this->position = 0;
        return true;
    }

    function stream_read($count) {
        $ret = substr(self::$var[$this->varName], $this->position, $count);
        $this->position+= strlen($ret);
	$this->aTime = time();
        return $ret;
    }

    function stream_write($data) {	
        $left = substr(self::$var[$this->varName], 0, $this->position);
        $right = substr(self::$var[$this->varName], $this->position + strlen($data));
        self::$var[$this->varName] = $left . $data . $right;
        $this->position+= strlen($data);
        $this->aTime = time();
        $this->mTime = time();
        return strlen($data);
    }

    function stream_tell() {
        return $this->position;
    }

    function stream_eof() {
        return $this->position >= strlen(self::$var[$this->varName]);
    }
	
    function stream_stat() {
	return $this->getStat();
    }
	
    function getStat() {
	if(!isset(self::$var[$this->varName]))
	    return;
	$stat = array(
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => 01700, 
            'nlink'   => 0,
            'uid'     => 0, 
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => strlen(self::$var[$this->varName]),
            'atime'   => $this->aTime ? $this->aTime : time(),
            'mtime'   => $this->mTime ? $this->mTime : time(),
            'ctime'   => $this->cTime ? $this->cTime : time(),
            'blksize' => 0,
            'blocks'  => 0
        );
	return array_merge($stat, array_values($stat));
    }
	
    function url_stat($path, $flags) {
        $this->varName = $this->get_key($path);
        return $this->getStat();
    }

    function stream_seek($offset, $whence)  {
        switch ($whence) {
        case SEEK_SET:
            if ($offset < strlen(self::$var[$this->varName]) && $offset >= 0) {
                 $this->position = $offset;
                 return true;
            } else {
                 return false;
            }
            break;
        case SEEK_CUR:
            if ($offset >= 0) {
                 $this->position+= $offset;
                 return true;
            } else {
                 return false;
            }
            break;
        case SEEK_END:
            if (strlen(self::$var[$this->varName]) + $offset >= 0) {
                $this->position = strlen(self::$var[$this->varName]) + $offset;
                return true;
            } else {
                return false;
            }
            break;
        default:
            return false;
        }
    }
    /*
    function stream_cast($cast_as) {
	echo '!!!!!';
	var_dump($cast_as);
	echo '!!!!!';
    }*/
    
    public static function register($name = 'var'){
        @stream_wrapper_register($name, __CLASS__);
    }
}
