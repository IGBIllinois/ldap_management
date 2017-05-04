<?php
ini_set("display_errors",1);
chdir(dirname(__FILE__));
set_include_path(get_include_path() . ':../libs');
function __autoload($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
}

include_once '../conf/settings.inc.php';

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
	$adldap = new ldap(__AD_LDAP_HOST__,false,__AD_LDAP_PORT__,__AD_LDAP_PEOPLE_OU__);
	$users = user::get_all_users($ldap);

	foreach($users as $uid){
		$user = new user($ldap,$uid);
				
		$filter = "(uid=".$uid.")";
		$attributes = array('uiucEduPhInactiveDate');
		$results = $adldap->search($filter,"",$attributes);
		if( !( $results && (($results['count']>0 && $results[0]['count']==0)||($results['count']==0)) ) ){
			echo $user->get_username()." ";
			if($user->get_leftcampus() == false){
				echo "\n";
				$user->set_leftcampus(true);
			} else {
				echo " Skipping\n";
			}
		} else {
			if($user->get_leftcampus() == true){
				$user->set_leftcampus(false);
			}
		}
	}
}