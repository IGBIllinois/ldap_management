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

	// Connect to ldap
	Ldap::init(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
	$users = User::all();
	$digest_expired = '';
	foreach($users as $uid){
		$user = new User($uid);
		if(!($user->isClassroom()) && $user->isExpired()){
			$digest_expired .= $user->getUsername()."<br>";
		}
	}

	if(strlen($digest_expired)>0){
		// Send a digest to help
		$subject = "Expired IGB Users";
		$to = "help@igb.illinois.edu";
		$emailmessage = "The following users' accounts are expired:<br><br>".$digest_expired;
		$headers = "From: do-not-reply@igb.illinois.edu\r\n";
		$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
		mail($to,$subject,$emailmessage,$headers," -f " . __ADMIN_EMAIL__);
		echo "\nDone.\n";
	}

}