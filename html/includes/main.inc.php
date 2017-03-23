<?php
	ini_set('display_errors',1);
set_include_path(get_include_path().":../libs:includes/PHPExcel_1.8.0/Classes");
include_once('../conf/settings.inc.php');
function my_autoloader($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
	// Load extension classes
	if(file_exists("../extensions/".substr($class_name,4)."/".$class_name.".class.inc.php")){
		require_once "../extensions/".substr($class_name,4)."/".$class_name.".class.inc.php";
	}
}

spl_autoload_register('my_autoloader');

extensions::init();

$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
$adldap = new ldap(__AD_LDAP_HOST__,false,__AD_LDAP_PORT__,__AD_LDAP_PEOPLE_OU__);
?>