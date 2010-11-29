<?php
/**
 *
 * @package util
 */
class AppClassLoader {
    private $classMap = array();
    private $appDir;
    private $externDir = array();
    private $cacheFile;
    
    function __construct($appDir, $cacheFile = null) {
        if (is_null($cacheFile)) {
            $cacheFile = $appDir . '/conf/classes.conf.php';
        }
        $this->appDir = $appDir;
        $this->cacheFile = $cacheFile;
        try {
            $this->classMap = @include $cacheFile;
        } catch (Exception $e) {
            //do nothing
        }
		
        if (empty($this->classMap) || !is_array($this->classMap)) {
            $this->classMap = array();
            $this->scan($appDir);
        }
        spl_autoload_register(array($this, 'load'));
    }
    
    function load($class) {
        if (isset($this->classMap[$class])) {
            require $this->classMap[$class];
        }
    }
    
    function pathOf($class) {
	return isset($this->classMap[$class]) ? $this->classMap[$class] : '';
    }
	
    function addExternDir($dir) {
        $this->externDir[]= $dir;
    }
    
    function writeCache($useAppPathConstant = true) {
        if (!$useAppPathConstant) {
            $content = "<?php\nreturn " . var_export($this->classMap, true) . ';';
            file_put_contents($this->cacheFile, $content);
            return;
        }
        
        $content = "<?php\nreturn array(\n";
        foreach ($this->classMap as $class => $path) {
            $item = '';
            if (StringUtil::beginWith($path, $this->appDir)) {
                $item = "'$class' => APP_PATH . '" . substr($path, strlen($this->appDir)) . "',\n";
            } else {
                $item = "'$class' => '$path',\n";
            }
            $content.= $item;
        }
        $content.= ");\n";
        if (!is_dir(dirname($this->cacheFile))) {
        	@mkdir(dirname($this->cacheFile),0755,true);
        }
        file_put_contents($this->cacheFile, $content);
        return;
    }
    
    function scan() {
        $this->_scan($this->appDir);
        foreach ($this->externDir as $dir) {
            $this->_scan($dir);
        }
        $this->writeCache();
    }
    
    private function _scan($dir) {
        foreach (glob("$dir/*", GLOB_ONLYDIR) as $d) {
            $this->_scan($d);
        }
        foreach (glob("$dir/*.php") as $f) {
        	$t = strrchr($f, '/');
            $content = file_get_contents($f);
            $tokens = token_get_all($content);
            $count = count($tokens);
            for ($i = 0; $i < $count; $i++) {
                if (is_array($tokens[$i]) && ($tokens[$i][0] == T_CLASS || $tokens[$i][0] == T_INTERFACE)) {
                    $i++;
                    $class = $tokens[$i + 1][1];
                    if (isset($this->classMap[$class])) {
                        continue;
                    }
                    $this->classMap[$class] = $f;
                }
            }
        }
    }
}

