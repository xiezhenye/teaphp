<?php
/**
 *
 */
class ModuleBuilder {
	function build($moduleName, $root) {
		mkdir($root."/modules/$moduleName");
		mkdir($root."/modules/$moduleName/actions");
		mkdir($root."/modules/$moduleName/models");
		mkdir($root."/modules/$moduleName/templates");
		$types = array('front', 'backend', 'admin', 'block');
		foreach ($types as $type) {
			mkdir($root."/modules/$moduleName/templates/$type");
			$class = StringUtil::camelize($moduleName).ucfirst($type);
			$code = "<?php
class $class extends BaseAction {
	
}";
			file_put_contents($root."/modules/$moduleName/actions/$class.class.php", $code);
		}
		

		
	}
	
}


