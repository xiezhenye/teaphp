<?php
define ('CORE_LIB_PATH', str_replace('\\', '/', dirname(__FILE__)));

class CoreClassLoader {
    static $classMap;
    static $cacheFile;
    
    static function init() {
        self::$cacheFile = CORE_LIB_PATH . '/../data/classes.conf.php';
        try {
            self::$classMap = @include self::$cacheFile;
        } catch (Exception $e) {
            //do nothing
        }
        if (!is_array(self::$classMap)) {
            self::$classMap = array();
            self::scan(CORE_LIB_PATH);
        }
        spl_autoload_register(array(__CLASS__, 'load'));
    }
    
    static function load($class) {
        if (isset(self::$classMap[$class])) {
            require_once(self::$classMap[$class]);
        }
    }
    
    static function writeCache() {
        $content = "<?php\nreturn array(\n";
        foreach (self::$classMap as $class => $path) {
            $item = '';
            if (substr($path, 0, strlen(CORE_LIB_PATH)) == CORE_LIB_PATH) {
                $item = "'$class' => CORE_LIB_PATH . '" . substr($path, strlen(CORE_LIB_PATH)) . "',\n";
            }
            $content.= $item;
        }
        $content.= ");\n";
        file_put_contents(self::$cacheFile, $content);
        return;
    }
    
    static function scan() {
        self::_scan(CORE_LIB_PATH);
    }
    
    static function _scan($dir) {
        foreach (glob("$dir/*", GLOB_ONLYDIR) as $d) {
            self::_scan($d);
            foreach (glob("$d/*.php") as $f) {
                $t = strrchr($f, '/');
				$content = file_get_contents($f);
				$tokens = token_get_all($content);
				$count = count($tokens);
				for ($i = 0; $i < $count; $i++) {
					if (is_array($tokens[$i]) && ($tokens[$i][0] == T_CLASS || $tokens[$i][0] == T_INTERFACE)) {
						$i++;
						$class = $tokens[$i + 1][1];
						if (isset(self::$classMap[$class])) {
							continue;
						}
		                self::$classMap[$class] = $f;
					}
				}
            }
        }
        self::writeCache();
    }
}
CoreClassLoader::init();
