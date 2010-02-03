<?php
include dirname(dirname(__FILE__)).'/lib/core.php';

$pwd = getcwd();
$cmd = isset($argv[1]) ? $argv[1] : '';
switch ($cmd) {
case 'initApp':
    if (!isset($argv[2])) {
        exit("missing app name\n");
    }
    $appName = $argv[2];
    include CORE_LIB_PATH.'/cli/AppBuilder.class.php';
    $builder = new AppBuilder();
    $builder->build($appName, $pwd);
    echo "initialized project $appName on $pwd.\n";
    break;

case 'buildModel':
    if (!isset($argv[2])) {
        exit("missing db config name\n");
    }
    if (!isset($argv[3])) {
        exit("missing table name\n");
    }
    $dbKey = $argv[2];
	$table = $argv[3];
	include CORE_LIB_PATH.'/cli/ModelBuilder.class.php';
    $builder = new ModelBuilder();
    $builder->build($pwd, $dbKey, $table);
    echo "created model config from table $table.\n";
	break;

case 'buildAllModels':
    if (!isset($argv[2])) {
        exit("missing db config name\n");
    }
    $dbKey = $argv[2];
	include CORE_LIB_PATH.'/cli/ModelBuilder.class.php';
    $builder = new ModelBuilder();
	$builder->buildAll($pwd, $dbKey);
	echo "created model configs from tables.\n";
	break;

case 'buildModule':
	$module = $argv[2];
	include CORE_LIB_PATH.'/cli/ModuleBuilder.class.php';
	$builder = new ModuleBuilder();
	$builder->build($module, $pwd);
	echo "initialized module $module.\n";
	break;
default:
    echo "initApp\nbuildModel\nbuildAllModels\nbuildModule\n";
    break;
}




