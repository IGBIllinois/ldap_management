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

function cmp($a,$b){
	if(isset($a['uid'][0]) && isset($b['uid'][0])){
		if($a['uid'][0] == $b['uid'][0]){
			return 0;
		}
		return ($a['uid'][0]<$b['uid'][0]) ? -1 : 1;
	}
	return 0;
}

$sapi_type = php_sapi_name();
// If run from command line
if ($sapi_type != 'cli') {
	echo "Error: This script can only be run from the command line.\n";
} else {
	// Connect to ldap
	$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
	
	$filter = "(postalAddress=*)";
	$ou = __LDAP_PEOPLE_OU__;
	$attributes = array('uid','postalAddress');
	$results = $ldap->search($filter, $ou, $attributes);
	unset($results['count']);
	usort($results,"cmp");
	for ($i=0; $i<count($results); $i++){
		echo $results[$i]['uid'][0].": ".$results[$i]['postaladdress'][0]."\n";
	}
}