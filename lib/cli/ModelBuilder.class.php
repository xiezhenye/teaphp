<?php
include CORE_LIB_PATH.'/model/mysql/Mysql.class.php';
include CORE_LIB_PATH.'/model/mysql/MysqlResultSet.class.php';
include CORE_LIB_PATH.'/util/StringUtil.class.php';

/**
 *
 */
class ModelBuilder {
	
	function build($pwd, $dbKey, $table) {
	    $conf = include $pwd . '/conf/resource.conf.php';
		$dbConf = $conf['database'][$dbKey];
		$db = new Mysql($dbConf);
		
		$modelConfPath = $pwd . '/conf/models.conf.php';
		$old =  (array) include $modelConfPath;
		
		list($class, $conf) = $this->confFromTable($db, $table);
		
		$old[$class] = $conf;
		
		$ret = var_export($old, true);
		$ret = preg_replace('/=>\s+array/', '=> array', $ret);
		$ret = str_replace("),", "),\n", $ret);
		$ret = "<?php\n\nreturn $ret;\n";
		file_put_contents($modelConfPath, $ret);
	}
	
	function buildAll($pwd, $dbKey) {
		$conf = include $pwd . '/conf/resource.conf.php';
		$dbConf = $conf['database'][$dbKey];
		$db = new Mysql($dbConf);
		
		$modelConfPath = $pwd . '/conf/models.conf.php';
		$old =  (array) include $modelConfPath;
		
		$sql = "show tables";
		$rs = $db->query($sql);
		foreach ($rs as $row) {
			$table = current($row);
			list($class, $conf) = $this->confFromTable($db, $table);
			$old[$class] = $conf;
		}
			
		$ret = var_export($old, true);
		$ret = preg_replace('/=>\s+array/', '=> array', $ret);
		$ret = str_replace("),", "),\n", $ret);
		$ret = "<?php\n\nreturn $ret;\n";
		file_put_contents($modelConfPath, $ret);
	}
	
	function confFromTable($db, $table) {
		$sql = "desc ".$db->addDelimiter($table)."";
		$rs = $db->query($sql);
		
		$fields = array();
		$conf = array();
		foreach ($rs as $row) {
			$property = StringUtil::camelize($row['Field']);
			$property[0] = strtolower($property[0]);
			$fields[$property] = array('field'=>$row['Field']);
			if ($row['Key'] == 'PRI') {
				$conf['id'] = $property;
			}
		}
		$conf['properties'] = $fields;
		$conf['table'] = $table;
		
		$class = StringUtil::singluarize(StringUtil::camelize($table));
		return array($class, $conf);
	}
}


