<?php
ini_set("display_errors",1);
chdir(dirname(__FILE__));
set_include_path(get_include_path() . ':../libs');
function __autoload($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
	
	// Load extension classes
	if(file_exists("../extensions/".substr($class_name,4)."/".$class_name.".class.inc.php")){
		require_once "../extensions/".substr($class_name,4)."/".$class_name.".class.inc.php";
	}
}

include_once '../conf/settings.inc.php';
extensions::init();

$sapi_type = php_sapi_name();
// If run from command line
if ($sapi_type != 'cli') {
	echo "Error: This script can only be run from the command line.\n";
} else {
	echo "Analyzing users...\n";
	
	// Connect to ldap
	$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
	$ldap->set_bind_user(__LDAP_BIND_USER__);
	$ldap->set_bind_pass(__LDAP_BIND_PASS__);
	$users = user::get_all_users($ldap);

	foreach($users as $uid){
		$user = new user($ldap,$uid);
		echo $user->get_username().": \t";
		if(ext_crashplan::has_crashplan($uid)){
			// Crashplan inactive
			if($user->get_crashplan() == false){
				echo "crashplan activated\n";
 				$user->set_crashplan(true);
			} else {
				echo "crashplan active (already knew)\n";
			}
		} else {
			if($user->get_crashplan() == true){
				echo "crashplan deactivated\n";
 				$user->set_crashplan(false);
			} else {
				echo "crashplan inactive (already knew)\n";
			}
		}
	}
}