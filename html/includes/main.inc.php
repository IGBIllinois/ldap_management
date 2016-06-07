<?php
	ini_set('display_errors',1);
set_include_path(get_include_path().":../libs:includes/PHPExcel_1.8.0/Classes");
include_once('../conf/settings.inc.php');
function my_autoloader($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
}

spl_autoload_register('my_autoloader');

$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
?>