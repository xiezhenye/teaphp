<?php

/**
 * 应用基类
 *
 */
class App {
    private $confPath = '';
    private $conf = array();
    private $path;
    private $classLoader = null;
    private $dispatcher = null;
    private $repositories = array();
    
    /**
     * 初始化
     *
     * @param string $confPath
     */
    function __construct($path) {
        $this->path = $path;
        $this->confPath = $this->path.'/conf';
    }
    
    /**
     * 根据配置得到数据库实例
     *
     * @param string $name 数据库配置名
     * @return Mysql
     */
    function getDB($name = 'default') {
        static $db = array();
        if (!isset($db[$name])) {
            $dbConf = $this->conf('resource', 'database');
            if (!isset($dbConf[$name]['host']) || !isset($dbConf[$name]['name'])) {
                return null;
            }
            $db[$name] = new Mysql($dbConf[$name]);
        }
        return $db[$name];
    }
    
    /**
     * 返回类所在的模块
     * 对非模块中的类返回 null
     * @return string
     */
    function getClassModule($class_name) {
        $path = $this->classLoader->pathOf($class_name);
        $path = substr($path, strlen($this->path));
        
        if (!StringUtil::beginWith($path, '/modules/')) {
            return null;
        }
        $arr = explode('/', $path);
        return $arr[2];
    }
    
    /**
     * 根据配置得到仓储实例
     *
     * @param string $class
     * @return Repository
     */
    function getRepository($class) {
        $module = $this->getClassModule($class);
        if (!$module) {
            throw new Exception("class '$class' not defined.");
        }
        $conf = $this->moduleConf($module, $class.'.model');
        
        if (!$conf) {
            throw new Exception("no such model '$class' config in module '$module'.");
        }
        
        if (!isset($this->repositories[$class])) {
            if (isset($conf['repository'])) {
                $repoClass = $conf['repository'];
                $ret = new $repoClass();
            } else {
                $ret = new Repository();
            }
            $ret->setClass($class);
            $ret->setConfig($conf);
            $dbName = isset($conf['db']) ? $conf['db'] : 'default';
            $ret->setDB($this->getDB($dbName));
            $this->repositories[$class] = $ret;
        }
        return $this->repositories[$class];
    }
    
    /**
     * 根据配置得到分派器实例
     *
     * @return Dispatcher
     */
    function getDispatcher() {
        if (is_null($this->dispatcher)) {
            $this->dispatcher = new Dispatcher($this);
        }
        return $this->dispatcher;
    }
    
    function conf($confName, $path = '', $default = null) {
        if (!isset($this->conf[$confName])) {
            $this->conf[$confName] = include $this->confPath.'/'.$confName.'.conf.php';
        }
        $ret = $this->conf[$confName];
        return $this->arrayPath($ret, $path, $default);
    }
    
    function moduleConf($module, $conf_name, $path = '', $default = null) {
        $key = "$module/$conf_name";
        if (!isset($this->conf[$key])) {
            $conf_file = $this->path.'/modules/'.$module.'/conf/'.$conf_name.'.conf.php';
            $this->conf[$key] = include $conf_file;
        }
        $ret = $this->conf[$key];
        return $this->arrayPath($ret, $path, $default);
    }
    
    protected function arrayPath($array, $path, $default = null) {
        $ret = $array;
        $pathArr = array_filter(explode('/', $path));
        foreach ($pathArr as $key) {
            if (!isset($ret[$key])) {
                return $default;
            }
            $ret = $ret[$key];
        }
        return $ret;
    }
    
    function getClassLoader() {
        if (is_null($this->classLoader)) {    
            $cache_file = $this->conf('app', 'class_loader_cache');
            $this->classLoader = new AppClassLoader($this->path, $cache_file);
        }
        return $this->classLoader;
    }
    
    function path() {
        return $this->path;
    }
    
    function modules() {
        $dirs = glob($this->path.'/modules/*', GLOB_ONLYDIR);
        $ret = array();
        foreach ($dirs as $dir) {
            $ret[]= substr(strrchr($dir, '/'), 1);
        }
        return $ret;
    }
}
