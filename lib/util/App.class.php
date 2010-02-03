<?php

/**
 * 应用基类
 *
 */
class App {
    private $confPath = '';
    private $conf = array();
    private $path;
    
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
        $db = array();
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
     * 根据配置得到仓储实例
     *
     * @param string $class
     * @return Repository
     */
    function getRepository($class) {
        $repository = array();
        $conf = $this->conf('models', $class);
        if (!$conf) {
            throw new Exception("no such model '$class' config");
        }
        if (!isset($repository[$class])) {
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
            $repository[$class] = $ret;
        }
        return $repository[$class];
    }
    
    /**
     * 根据配置得到分派器实例
     *
     * @return Dispatcher
     * @deprecated
     */
    function getDispatcher() {
        $dispatcher = new Dispatcher(APP_PATH, $this->conf('app'));
        return $dispatcher;
    }
    
    function conf($confName, $path = '', $default = null) {
        if (!isset($this->conf[$confName])) {
            $this->conf[$confName] = include $this->confPath.'/'.$confName.'.conf.php';
        }
        $ret = $this->conf[$confName];
        $pathArr = array_filter(explode('/', $path));
        foreach ($pathArr as $key) {
            if (!isset($ret[$key])) {
                return $default;
            }
            $ret = $ret[$key];
        }
        return $ret;
    }
    
    function getClassLoader($cache_file = null) {
        $loader = new AppClassLoader($this->path, $cache_file);
        return $loader;
    }
}
