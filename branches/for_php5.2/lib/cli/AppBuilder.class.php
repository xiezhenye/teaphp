<?php
/**
 *
 */
class AppBuilder {
    function build($appName, $root) {
        $this->mkdir($appName, $root);
        $this->writeApp($appName, $root);
        $this->writeIndex($appName, $root);
        $this->writeConf($appName, $root);
    }
    
    function mkdir($appName, $root) {
        file_put_contents($root.'/readme.txt', "Project $appName:\n");
        $dirs = array(
            '/bin',
            '/conf',
            '/doc',
            '/lib',
            '/modules',
            '/templates',
            '/test',
            '/www',
        );
        foreach ($dirs as $dir) {
            mkdir($root.$dir, 0775, true);
        }
    }
    
    function writeApp($appName, $root) {
        $appFile = "<?php
class $appName extends App {
    
}";
        file_put_contents($root."/lib/$appName.class.php", $appFile);
    }
    
    function writeIndex($appName, $root) {
        $indexFile = '<?php
define("APP_PATH", dirname(dirname(__FILE__)));
define("DEBUG_MODE", 1);
require "'.CORE_LIB_PATH.'/core.php";

AppClassLoader::init(APP_PATH);

if (defined("DEBUG_MODE")) {
    AppClassLoader::scan();
}
require CORE_LIB_PATH."/util/App.class.php";
require APP_PATH."/lib/'.$appName.'.class.php";
function app() {
    static $app = null;
    if (is_null($app)) {
        $app = new '.$appName.'(APP_PATH."/conf");
    }
    return $app;
}

$dispatcher = new Dispatcher(APP_PATH, app()->conf("app"));
$dispatcher->dispatch($_SERVER["REQUEST_URI"]);
';
        file_put_contents($root."/www/index.php", $indexFile);
    }
    
    function writeConf($appName, $root) {
        $resconf = "<?php
return array(
    'database' => array(
        'default' => array(
            'host' => 'localhost',
            'name' => 'db_name',
            'username' => 'db_user',
            'password' => 'db_password',
            'charset' => 'utf8'
        ),
    )
);
";
        file_put_contents($root."/conf/resource.conf.php", $resconf);
        $appconf = "<?php
return array(
    'domain' => array(
        'root'=>'example.com'
    ),
    
    'route' => array(
        '^/admin/(\w+)/(\d+)/(\w+)' => array('module', 'id', 'method',  'type'=>'admin'),
        '^/admin/(\w+)/(\d+)' => array('module', 'id', 'method'=>'item',  'type'=>'admin'),
        '^/admin/(\w+)/(\w+)' => array('module', 'method', 'type'=>'admin'),
        '^/admin/(\w+)' => array('module', 'type'=>'admin'),
        
        '^/(\w+)/(\d+)/(\w+)' => array('module', 'id', 'method',  'type'=>'backend'),
        '^/(\w+)/(\d+)' => array('module', 'id', 'method'=>'item',  'type'=>'backend'),
        '^/(\w+)/(\w+)' => array('module', 'method', 'type'=>'backend'),
        '^/(\w+)' => array('module', 'type'=>'backend'),
    ),
    
    'actions' => array(
        'backend' => array(
            'view' => array(
                'PHPView' => array(
                )
            )
        ),
        
        'admin' => array(
            'view' => array(
                'PHPView' => array(
                )
            )
        ),
    ),
    
    'url' => array(
        'base' => '/'
    ),
);
";
        file_put_contents($root."/conf/app.conf.php", $appconf);
        $modelconf =
'<?php
return array (
);
';
        file_put_contents($root."/conf/models.conf.php", $modelconf);
    }
}


